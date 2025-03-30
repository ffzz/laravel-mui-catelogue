<?php

namespace App\Services\Content;

use App\Data\ContentDataFactory;
use App\Jobs\RefreshContentCache;
use App\Services\Acorn\AcornApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ContentService
{
    protected AcornApiService $acornApiService;
    protected bool $enableCache;
    protected int $cacheTtl;
    protected string $cacheVersion;
    protected bool $backgroundRefresh;
    protected float $refreshThreshold;

    public function __construct(AcornApiService $acornApiService)
    {
        $this->acornApiService = $acornApiService;
        $this->enableCache = config('acorn.cache.enabled', true);
        $this->cacheTtl = config('acorn.cache.ttl', 900); // 15 minutes default
        $this->cacheVersion = config('acorn.cache.version', 'v1');
        $this->backgroundRefresh = config('acorn.cache.background_refresh.enabled', true);
        $this->refreshThreshold = config('acorn.cache.background_refresh.threshold', 0.8);
    }

    /**
     * Get cache TTL based on content type
     */
    protected function getCacheTtlByContentType(?string $contentType = null): int
    {
        if (!$contentType) {
            return $this->cacheTtl;
        }

        $contentTypeKey = strtolower($contentType);
        $ttlMap = config('acorn.cache.content_types', []);

        return $ttlMap[$contentTypeKey] ?? $this->cacheTtl;
    }

    /**
     * Create cache key with version
     */
    protected function createCacheKey(string $baseKey): string
    {
        return "{$baseKey}:{$this->cacheVersion}";
    }

    /**
     * Check if cache should be refreshed in background
     */
    protected function shouldRefreshInBackground(string $cacheKey): bool
    {
        if (!$this->backgroundRefresh) {
            return false;
        }

        // Check if cache exists and when it was created
        if (!Cache::has($cacheKey)) {
            return false;
        }

        // Get cached data and check if it contains the _cached_at field
        $cachedData = Cache::get($cacheKey);
        if (!is_array($cachedData) || !isset($cachedData['_cached_at'])) {
            return false;
        }

        // Calculate the elapsed time the cache has existed (as a percentage of TTL)
        $cachedAt = $cachedData['_cached_at'];
        $now = time();
        $elapsedTime = $now - $cachedAt;

        // Get the cache TTL for this content type
        $contentType = null;
        if (isset($cachedData['items'][0]['contentType'])) {
            $contentType = $cachedData['items'][0]['contentType'];
        }
        $ttl = $this->getCacheTtlByContentType($contentType);

        if ($ttl <= 0) {
            return false;
        }

        $elapsedPercentage = $elapsedTime / $ttl;

        // If the elapsed time exceeds our threshold, we should refresh
        return $elapsedPercentage >= $this->refreshThreshold;
    }

    /**
     * Refresh cache in background
     */
    protected function triggerBackgroundRefresh(string $method, array $params = []): void
    {
        Queue::push(RefreshContentCache::class, [
            'method' => $method,
            'params' => $params,
        ]);

        Log::info('Triggered background cache refresh', [
            'method' => $method,
            'params' => $params,
        ]);
    }

    /**
     * Get content catalogue
     */
    public function getCatalogue(int $page = 1, ?int $perPage = null, array $filters = []): array
    {
        // Check if we should bypass cache
        $bypassCache = $filters['no_cache'] ?? false;
        if (isset($filters['no_cache'])) {
            unset($filters['no_cache']);
        }

        // Build parameters
        $params = array_merge($filters, [
            'page' => $page,
            'perPage' => $perPage,
        ]);

        // Determine content type for cache TTL
        $contentType = $filters['contentType'] ?? null;
        $cacheTtl = $this->getCacheTtlByContentType($contentType);

        // Create cache key based on all parameters
        $cacheKeyBase = "content_catalogue:" . md5(json_encode($params));
        $cacheKey = $this->createCacheKey($cacheKeyBase);

        // Try to retrieve from cache if enabled and not bypassed
        if ($this->enableCache && !$bypassCache && Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            Log::info('Returning cached content catalogue', ['params' => $params]);

            // Check if we should refresh the cache in background
            if ($this->shouldRefreshInBackground($cacheKey)) {
                Log::info('Cache is getting stale, triggering background refresh', [
                    'content_type' => $contentType,
                    'params' => $params,
                ]);

                $this->triggerBackgroundRefresh('getCatalogue', [
                    'page' => $page,
                    'perPage' => $perPage,
                    'filters' => $filters,
                ]);
            }

            return $cachedData;
        }

        // Retrieve data from API
        $response = $this->acornApiService->getExternalCatalogue($params);

        if (!$response['success']) {
            Log::error('Failed to get content catalogue', [
                'error' => $response['error'] ?? 'Unknown error',
                'status_code' => $response['status_code'] ?? 500,
            ]);
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to get content catalogue',
                'status_code' => $response['status_code'] ?? 500,
            ];
        }

        // Process data - handle nested structure from ACORN API
        $responseData = $response['data'];

        // Handle different response structures
        // Structure 1: { status: "Complete", data: { items: [...], ... } }
        if (isset($responseData['status']) && $responseData['status'] === 'Complete' && isset($responseData['data']['items'])) {
            $data = $responseData['data'];
            $items = $data['items'];
        }
        // Structure 2: { data: { items: [...], ... } }
        else if (isset($responseData['data']) && isset($responseData['data']['items'])) {
            $data = $responseData['data'];
            $items = $data['items'];
        }
        // Structure 3: { items: [...], ... }
        else if (isset($responseData['items'])) {
            $data = $responseData;
            $items = $data['items'];
        } else {
            Log::error('Unexpected API response format', [
                'data_structure' => json_encode($responseData),
            ]);
            return [
                'success' => false,
                'error' => 'Unexpected API response format',
                'status_code' => 500,
            ];
        }

        // Process pagination metadata
        $metadata = [
            'totalItems' => $data['total_items'] ?? count($items),
            'currentPage' => $data['current_page'] ?? $page,
            'perPage' => $data['per_page'] ?? $perPage,
            'totalPages' => $data['total_pages'] ?? ceil(count($items) / ($perPage ?: 10)),
            'nextPageUrl' => $data['next_page_url'] ?? null,
            'previousPageUrl' => $data['previous_page_url'] ?? null,
        ];

        // Create content data collection
        $contentCollection = ContentDataFactory::createCollectionFromData($items);

        // Convert objects to arrays for caching
        $contentArrays = [];
        foreach ($contentCollection as $content) {
            $contentArrays[] = $content->toArray();
        }

        // Prepare results
        $result = [
            'success' => true,
            'items' => $contentCollection, // Keep original objects for the response
            'metadata' => $metadata,
        ];

        // Prepare a cacheable version with arrays instead of objects
        $cacheableResult = [
            'success' => true,
            'items' => $contentArrays, // Use arrays for caching
            'metadata' => $metadata,
            '_is_cached' => true,
            '_cached_at' => time(),
        ];

        // Cache results
        if ($this->enableCache && !$bypassCache) {
            Cache::put($cacheKey, $cacheableResult, $cacheTtl);
            Log::info('Cached content catalogue', [
                'params' => $params,
                'ttl' => $cacheTtl,
                'content_type' => $contentType,
            ]);
        }

        return $result;
    }

    /**
     * Get content item by specific ID
     */
    public function getContentItem(int $id, bool $bypassCache = false): array
    {
        // Create cache key with version
        $cacheKeyBase = "content_item:{$id}";
        $cacheKey = $this->createCacheKey($cacheKeyBase);

        // Try to retrieve from cache if enabled and not bypassed
        if ($this->enableCache && !$bypassCache && Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            Log::info('Returning cached content item', ['id' => $id]);

            // Check if we should refresh the cache in background
            if ($this->shouldRefreshInBackground($cacheKey)) {
                Log::info('Content item cache is getting stale, triggering background refresh', [
                    'id' => $id,
                ]);

                $this->triggerBackgroundRefresh('getContentItem', [
                    'id' => $id,
                    'bypassCache' => true,
                ]);
            }

            // If this is cached data, we need to reconstruct the Data object
            if (isset($cachedData['_is_cached']) && $cachedData['_is_cached']) {
                $itemData = $cachedData['item'];
                return [
                    'success' => true,
                    'item' => ContentDataFactory::createFromData($itemData),
                ];
            }

            return $cachedData;
        }

        // Retrieve from API
        $response = $this->acornApiService->getContentById($id);

        if (!$response['success']) {
            Log::error('Failed to get content item', [
                'id' => $id,
                'error' => $response['error'] ?? 'Unknown error',
                'status_code' => $response['status_code'] ?? 500,
            ]);
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to get content item',
                'status_code' => $response['status_code'] ?? 500,
            ];
        }

        // Process data
        $data = $response['data'];

        // Check if we have a direct content item or a list with one item
        if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
            // It's a list, get the first item
            $contentData = ContentDataFactory::createFromData($data['items'][0]);
        } else if (isset($data['contentid']) || isset($data['id'])) {
            // It's a direct content item
            $contentData = ContentDataFactory::createFromData($data);
        } else {
            Log::error('Invalid content item data format', [
                'id' => $id,
                'data_structure' => json_encode($data),
            ]);
            return [
                'success' => false,
                'error' => 'Invalid content data format',
                'status_code' => 500,
            ];
        }

        // Determine content type for cache TTL
        $contentType = $contentData->contentType ?? null;
        $cacheTtl = $this->getCacheTtlByContentType($contentType);

        // Prepare results
        $result = [
            'success' => true,
            'item' => $contentData,
        ];

        // Prepare a cacheable version with array instead of object
        $cacheableResult = [
            'success' => true,
            'item' => $contentData->toArray(),
            '_is_cached' => true,
            '_cached_at' => time(),
            '_content_type' => $contentType,
        ];

        // Cache results
        if ($this->enableCache && !$bypassCache) {
            Cache::put($cacheKey, $cacheableResult, $cacheTtl);
            Log::info('Cached content item', [
                'id' => $id,
                'ttl' => $cacheTtl,
                'content_type' => $contentType,
            ]);
        }

        return $result;
    }

    /**
     * Get content items filtered by type
     */
    public function getContentByType(string $contentType, int $page = 1, ?int $perPage = null, bool $bypassCache = false): array
    {
        return $this->getCatalogue($page, $perPage, [
            'contentType' => $contentType,
            'no_cache' => $bypassCache,
        ]);
    }

    /**
     * Refresh cache for a specific content item or all items of a specific type
     */
    public function refreshCache(int $id = null, string $contentType = null): bool
    {
        Log::info('Manually refreshing cache', [
            'id' => $id,
            'content_type' => $contentType,
        ]);

        if ($id) {
            // Refresh specific content item
            $cacheKeyBase = "content_item:{$id}";
            $cacheKey = $this->createCacheKey($cacheKeyBase);

            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
                Log::info('Removed cache for content item', ['id' => $id]);
            }

            // Fetch fresh data
            $this->getContentItem($id, true);
            return true;
        }

        if ($contentType) {
            // Refresh all items of a specific type
            // This is more complex as we need to find all relevant cache keys
            $pattern = $this->createCacheKey("content_catalogue:*");

            // Unfortunately Laravel doesn't provide a direct way to list keys by pattern
            // An alternative would be to maintain a registry of cached content types
            // For now, we'll just fetch fresh data for the first page
            $this->getContentByType($contentType, 1, null, true);
            return true;
        }

        return false;
    }
}
