<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GetContentListRequest;
use App\Http\Requests\Api\V1\GetContentItemRequest;
use App\Http\Requests\Api\V1\RefreshCacheRequest;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContentController extends Controller
{
    protected ContentService $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Get content catalogue
     */
    public function index(GetContentListRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = $validated['page'] ?? 1;
        $perPage = $validated['perPage'] ?? config('acorn.api.per_page');
        $contentType = $validated['contentType'] ?? null;
        $bypassCache = $validated['noCache'] ?? false;

        // Build filters array
        $filters = [];
        if ($contentType) {
            $filters['contentType'] = $contentType;
        }
        if ($bypassCache) {
            $filters['no_cache'] = true;
        }

        Log::info('API request: Get content catalogue', [
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters,
        ]);

        $result = $this->contentService->getCatalogue($page, $perPage, $filters);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to get content catalogue',
            ], $result['status_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'items' => $result['items'],
            'metadata' => $result['metadata'] ?? [],
        ]);
    }

    /**
     * Get specific content item
     */
    public function show(int $id, GetContentItemRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $bypassCache = $validated['noCache'] ?? false;

        Log::info('API request: Get content item', [
            'id' => $id,
            'bypass_cache' => $bypassCache,
        ]);

        $result = $this->contentService->getContentItem($id, $bypassCache);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to get content item',
            ], $result['status_code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'item' => $result['item'],
        ]);
    }

    /**
     * Refresh cache for content items
     */
    public function refreshCache(RefreshCacheRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $id = $validated['id'] ?? null;
        $contentType = $validated['contentType'] ?? null;

        Log::info('API request: Refresh cache', [
            'id' => $id,
            'content_type' => $contentType,
        ]);

        $result = $this->contentService->refreshCache($id, $contentType);

        return response()->json([
            'success' => $result,
            'message' => $result
                ? 'Cache refreshed successfully'
                : 'Failed to refresh cache or no matching items found',
        ]);
    }
}
