<?php

namespace App\Services\Content;

use App\Data\ContentDataFactory;
use App\Enums\ContentType;
use App\Jobs\RefreshContentCache;
use App\Services\Acorn\AcornApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ContentService
{
    /**
     * Constants for cache key prefixes
     */
    private const CACHE_PREFIX_CATALOGUE = 'content_catalogue';
    private const CACHE_PREFIX_ITEM = 'content_item';

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
        dispatch(new RefreshContentCache($method, $params));

        Log::info('Triggered background cache refresh', [
            'method' => $method,
            'params' => $params,
        ]);
    }

    /**
     * Get content catalogue
     * 
     * This method fetches content catalogue data from:
     * - Cache (if available and not bypassed)
     * - API (if cache is bypassed or not available)
     * 
     * @param int $page Page number
     * @param int|null $perPage Items per page
     * @param array $filters Additional filters (contentType, no_cache, etc.)
     * @return array Result with content items and metadata
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
        $cacheKeyBase = self::CACHE_PREFIX_CATALOGUE . ":" . md5(json_encode($params));
        $cacheKey = $this->createCacheKey($cacheKeyBase);
        
        // If we're bypassing cache and the cache exists, remove it first
        if ($bypassCache && $this->enableCache && Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
            Log::info('Removed existing catalogue cache before refreshing', [
                'content_type' => $contentType,
                'page' => $page,
                'cache_key' => $cacheKey
            ]);
        }

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

        // If we reach here, we need to fetch from API because:
        // 1. Cache is disabled, or
        // 2. Cache is being bypassed, or
        // 3. Data is not in cache
        Log::info('Fetching content catalogue from API', [
            'content_type' => $contentType,
            'page' => $page,
            'bypass_cache' => $bypassCache
        ]);

        // Retrieve data from API
        $response = $this->acornApiService->getExternalCatalogue($params);

        if (!$response['success']) {
            Log::error('Failed to get content catalogue from API', [
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
            'totalPages' => $data['total_pages'] ?? ceil(($data['total_items'] ?? count($items)) / ($perPage ?: 10)),
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

        // Cache results - even if bypassCache is true, we want to update the cache
        // with fresh data after a refresh
        if ($this->enableCache) {
            Cache::put($cacheKey, $cacheableResult, $cacheTtl);
            Log::info('Cached content catalogue', [
                'params' => $params,
                'ttl' => $cacheTtl,
                'content_type' => $contentType,
                'was_refresh' => $bypassCache
            ]);
        }

        return $result;
    }

    /**
     * Get content item by specific ID
     * 
     * This method fetches a content item from:
     * - Cache (if available and not bypassed)
     * - API (if cache is bypassed or not available)
     * 
     * @param int $id Content item ID
     * @param bool $bypassCache Whether to bypass cache and fetch from API
     * @return array Result with content item data
     */
    public function getContentItem(int $id, bool $bypassCache = false): array
    {
        // Create cache key with version
        $cacheKeyBase = self::CACHE_PREFIX_ITEM . ":{$id}";
        $cacheKey = $this->createCacheKey($cacheKeyBase);
        
        // If we're bypassing cache and the cache exists, remove it first
        if ($bypassCache && $this->enableCache && Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
            Log::info('Removed existing content item cache before refreshing', [
                'id' => $id,
                'cache_key' => $cacheKey
            ]);
        }

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

        // If we reach here, we need to fetch from API because:
        // 1. Cache is disabled, or
        // 2. Cache is being bypassed, or
        // 3. Data is not in cache
        Log::info('Fetching content item from API', [
            'id' => $id,
            'bypass_cache' => $bypassCache
        ]);

        // Retrieve from API
        $response = $this->acornApiService->getContentById($id);

        if (!$response['success']) {
            Log::error('Failed to get content item from API', [
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

        // Cache results - even if bypassCache is true, we want to update the cache
        // with fresh data after a refresh
        if ($this->enableCache) {
            Cache::put($cacheKey, $cacheableResult, $cacheTtl);
            Log::info('Cached content item', [
                'id' => $id,
                'ttl' => $cacheTtl,
                'content_type' => $contentType,
                'was_refresh' => $bypassCache
            ]);
        }

        return $result;
    }

    /**
     * Get content items filtered by type
     * 
     * This is a convenience wrapper around getCatalogue that filters by contentType
     * 
     * @param string $contentType Content type to filter by
     * @param int $page Page number
     * @param int|null $perPage Items per page
     * @param bool $bypassCache Whether to bypass cache and fetch from API
     * @return array Result with content items and metadata
     */
    public function getContentByType(string $contentType, int $page = 1, ?int $perPage = null, bool $bypassCache = false): array
    {
        Log::info('Getting content by type', [
            'content_type' => $contentType,
            'page' => $page,
            'bypass_cache' => $bypassCache
        ]);
        
        $result = $this->getCatalogue($page, $perPage, [
            'contentType' => $contentType,
            'no_cache' => $bypassCache,
        ]);
        
        // Log the result for debugging purposes
        Log::info('Completed getting content by type', [
            'content_type' => $contentType,
            'success' => $result['success'] ?? false,
            'item_count' => isset($result['items']) ? count($result['items']) : 0
        ]);
        
        return $result;
    }

    /**
     * Refresh cache for content items
     * 
     * This method refreshes the cache for:
     * - A specific content item if ID is provided
     * - All items of a specific content type if contentType is provided
     * - All content items if neither ID nor contentType is provided
     * 
     * The refresh process works by:
     * 1. Removing any existing cached data for the specified content
     * 2. Fetching fresh data from the API
     * 3. Storing the new data in cache
     */
    public function refreshCache(?int $id = null, ?string $contentType = null): bool
    {
        Log::info('Manually refreshing cache', [
            'id' => $id,
            'content_type' => $contentType,
        ]);

        // Case 1: Refresh a specific content item by ID
        if ($id) {
            // Build cache key for this content item
            $itemCacheKey = $this->createCacheKey(self::CACHE_PREFIX_ITEM . ":{$id}");
            
            // Delete the existing cache if it exists
            if (Cache::has($itemCacheKey)) {
                Cache::forget($itemCacheKey);
                Log::info('Removed cache for content item', ['id' => $id]);
            }
            
            // Fetch new data from API and store in cache
            // The getContentItem method with bypassCache=true will:
            // 1. Skip reading from cache
            // 2. Fetch fresh data from API
            // 3. Store the result in cache with the same key
            $result = $this->getContentItem($id, true);
            
            Log::info('Refreshed cache for content item', [
                'id' => $id,
                'success' => $result['success'] ?? false
            ]);
            
            return true;
        }

        // Case 2: Refresh all items of a specific content type
        if ($contentType) {
            Log::info('Refreshing cache for content type', ['content_type' => $contentType]);
            
            // We can't easily find all cache keys for a specific content type
            // in Laravel without scanning all keys. Instead, we'll:
            
            // 1. Refresh the content type's dedicated catalogue
            Log::info('Refreshing catalogue data for content type', ['content_type' => $contentType]);
            $typeResult = $this->getContentByType($contentType, 1, null, true);
            
            // 2. Also refresh the general catalogue which might include this content type
            Log::info('Refreshing general catalogue that might contain this content type');
            $catalogueResult = $this->getCatalogue(1, null, ['no_cache' => true]);
            
            Log::info('Completed refreshing cache for content type', [
                'content_type' => $contentType,
                'type_success' => $typeResult['success'] ?? false,
                'catalogue_success' => $catalogueResult['success'] ?? false
            ]);
            
            return true;
        }

        // Case 3: Refresh all content types (when no specific ID or type provided)
        Log::info('Refreshing cache for all content types');
        
        $results = [];
        
        // 1. First, refresh the general catalogue with all content types
        Log::info('Refreshing general content catalogue');
        $results['general'] = $this->getCatalogue(1, null, ['no_cache' => true])['success'] ?? false;
        
        // 2. Then refresh each content type's specific catalogue
        foreach (ContentType::values() as $type) {
            Log::info('Refreshing specific content type catalogue', ['content_type' => $type]);
            $results[$type] = $this->getContentByType($type, 1, null, true)['success'] ?? false;
        }
        
        Log::info('Completed refreshing all content caches', ['results' => $results]);
        return true;
    }
}
