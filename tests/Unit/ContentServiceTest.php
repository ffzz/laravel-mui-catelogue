<?php

namespace Tests\Unit;

use App\Enums\ContentType;
use App\Services\Acorn\AcornApiService;
use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ContentServiceTest extends TestCase
{
    /** @var MockObject|AcornApiService */
    protected $acornApiServiceMock;
    
    protected $contentService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock of AcornApiService using PHPUnit's native mocking
        $this->acornApiServiceMock = $this->createMock(AcornApiService::class);
        
        // Create the content service with the mock API service
        $this->contentService = new ContentService($this->acornApiServiceMock);
        
        // Clear any existing cache
        Cache::flush();
    }

    /**
     * Test refreshing cache for a specific content item
     */
    public function testRefreshCacheForSpecificItem(): void
    {
        // Setup the API mock to return a successful response
        $this->acornApiServiceMock->method('getContentById')
            ->with(123)
            ->willReturn([
                'success' => true,
                'data' => [
                    'id' => 123,
                    'contenttype' => ContentType::COURSE->value,
                    'fullname' => 'Test Course',
                    // Other content properties...
                ]
            ]);
            
        // We need to use the same cache key logic as the service
        $cacheKey = "content_item:123:" . config('acorn.cache.version', 'v1');
            
        // Cache a sample item to be refreshed
        Cache::put($cacheKey, [
            '_is_cached' => true, 
            'item' => ['id' => 123, 'contenttype' => ContentType::COURSE->value]
        ], 60);

        // Make sure it's in cache
        $this->assertTrue(Cache::has($cacheKey), "Cache item should exist before refresh");

        // Call the refresh method
        $result = $this->contentService->refreshCache(123);

        // Assert the result
        $this->assertTrue($result, "Refresh operation should return true");
        
        // The item should be back in cache after refresh (with new data)
        $this->assertTrue(Cache::has($cacheKey), "Cache item should exist after refresh");
    }

    /**
     * Test refreshing cache for a specific content type
     */
    public function testRefreshCacheForContentType(): void
    {
        // Mock the API response for content type catalogue
        $this->acornApiServiceMock->method('getExternalCatalogue')
            ->willReturn([
                'success' => true,
                'data' => [
                    'items' => [
                        [
                            'id' => 123,
                            'contenttype' => ContentType::COURSE->value,
                            'fullname' => 'Test Course',
                        ]
                    ],
                    'total_items' => 1,
                    'current_page' => 1,
                    'total_pages' => 1,
                ]
            ]);

        // Call the refresh method
        $result = $this->contentService->refreshCache(null, ContentType::COURSE->value);

        // Assert the result
        $this->assertTrue($result, "Refresh by content type should return true");
    }

    /**
     * Test refreshing all caches
     */
    public function testRefreshAllCaches(): void
    {
        // Mock the API response for all content types
        $this->acornApiServiceMock->method('getExternalCatalogue')
            ->willReturn([
                'success' => true,
                'data' => [
                    'items' => [
                        [
                            'id' => 123,
                            'contenttype' => ContentType::COURSE->value,
                            'fullname' => 'Test Course',
                        ]
                    ],
                    'total_items' => 1,
                    'current_page' => 1,
                    'total_pages' => 1,
                ]
            ]);

        // Call the refresh method with no parameters
        $result = $this->contentService->refreshCache();

        // Assert the result
        $this->assertTrue($result, "Refresh all caches should return true");
    }
} 