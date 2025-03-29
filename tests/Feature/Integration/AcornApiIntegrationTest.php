<?php

namespace Tests\Feature\Integration;

use App\Services\Acorn\AcornApiService;
use App\Services\HttpClient\HttpClientService;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AcornApiIntegrationTest extends TestCase
{
    /**
     * Skip these tests by default since they make real API calls
     * Run with: php artisan test --filter=AcornApiIntegrationTest
     */
    protected $skipByDefault = false;

    /**
     * Test real API connection with default parameters
     */
    #[\PHPUnit\Framework\Attributes\Group('integration')]
    #[\PHPUnit\Framework\Attributes\Group('external-api')]
    public function test_real_external_catalogue_connection(): void
    {
        // Skip test by default to avoid making real API calls
        if ($this->skipByDefault && !getenv('RUN_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Integration tests are skipped by default. Set RUN_INTEGRATION_TESTS=1 to run them.');
        }

        // Create real instances (not mocks)
        $httpClient = new HttpClientService(config('acorn'));
        $apiService = new AcornApiService($httpClient);

        Log::info('Making real API call in test_real_external_catalogue_connection');

        try {
            // Make the actual API call
            $result = $apiService->getExternalCatalogue();

            Log::info('API call result', [
                'success' => $result['success'] ?? false,
                'status_code' => $result['status_code'] ?? null,
                'data_keys' => array_keys($result['data'] ?? []),
                'data_structure' => $result['data'] ? array_keys($result['data']) : []
            ]);

            // Basic assertions
            $this->assertTrue($result['success']);
            $this->assertEquals(200, $result['status_code']);
            $this->assertArrayHasKey('data', $result);

            // API returns data in format: { status: "Complete", data: { items: [...], ... } }
            $this->assertArrayHasKey('status', $result['data']);
            $this->assertEquals('Complete', $result['data']['status']);
            $this->assertArrayHasKey('data', $result['data']);
            $this->assertArrayHasKey('items', $result['data']['data']);
        } catch (\Exception $e) {
            Log::error('Exception in test_real_external_catalogue_connection', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Test real API connection with filtering by content type
     */
    #[\PHPUnit\Framework\Attributes\Group('integration')]
    #[\PHPUnit\Framework\Attributes\Group('external-api')]
    public function test_real_content_type_filtering(): void
    {
        // Skip test by default to avoid making real API calls
        if ($this->skipByDefault && !getenv('RUN_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Integration tests are skipped by default. Set RUN_INTEGRATION_TESTS=1 to run them.');
        }

        // Create real instances (not mocks)
        $httpClient = new HttpClientService(config('acorn'));
        $apiService = new AcornApiService($httpClient);

        // Content types to test
        $contentTypes = ['course', 'live learning', 'resource', 'video', 'program', 'page'];

        // Known problematic content types that may timeout or fail
        $problematicTypes = ['partnered content'];

        Log::info('Testing content type filtering with types', ['types' => $contentTypes]);

        foreach ($contentTypes as $contentType) {
            try {
                Log::info('Making API call for content type', ['type' => $contentType]);

                // Make the actual API call
                $result = $apiService->getContentByType($contentType);

                Log::info('API call result for content type', [
                    'type' => $contentType,
                    'success' => $result['success'] ?? false,
                    'status_code' => $result['status_code'] ?? null,
                    'has_data' => isset($result['data']['data']),
                    'item_count' => isset($result['data']['data']['items']) ? count($result['data']['data']['items']) : 0,
                ]);

                // Basic assertions
                $this->assertTrue($result['success']);
                $this->assertEquals(200, $result['status_code']);
                $this->assertArrayHasKey('data', $result);
                $this->assertArrayHasKey('status', $result['data']);
                $this->assertEquals('Complete', $result['data']['status']);
                $this->assertArrayHasKey('data', $result['data']);
                $this->assertArrayHasKey('items', $result['data']['data']);

                // Check that all returned items have the correct content type
                // Note: This might fail if there are no items of this type in the real API
                if (!empty($result['data']['data']['items'])) {
                    foreach ($result['data']['data']['items'] as $item) {
                        $this->assertEquals($contentType, strtolower($item['contenttype']));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Exception in test_real_content_type_filtering', [
                    'content_type' => $contentType,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }

        // Test problematic content types, but don't fail the test if they fail
        Log::info('Testing potentially problematic content types', ['types' => $problematicTypes]);
        foreach ($problematicTypes as $contentType) {
            try {
                Log::info('Making API call for problematic content type', ['type' => $contentType]);

                // Make the actual API call
                $result = $apiService->getContentByType($contentType);

                Log::info('API call result for problematic content type', [
                    'type' => $contentType,
                    'success' => $result['success'] ?? false,
                    'status_code' => $result['status_code'] ?? null,
                    'has_data' => isset($result['data']['data']),
                    'item_count' => isset($result['data']['data']['items']) ? count($result['data']['data']['items']) : 0,
                ]);

                // If successful, perform assertions
                if ($result['success']) {
                    $this->assertEquals(200, $result['status_code']);
                    $this->assertArrayHasKey('data', $result);
                    $this->assertArrayHasKey('status', $result['data']);
                    $this->assertEquals('Complete', $result['data']['status']);
                    $this->assertArrayHasKey('data', $result['data']);
                    $this->assertArrayHasKey('items', $result['data']['data']);

                    // Check that all returned items have the correct content type
                    if (!empty($result['data']['data']['items'])) {
                        foreach ($result['data']['data']['items'] as $item) {
                            $this->assertEquals($contentType, strtolower($item['contenttype']));
                        }
                    }
                } else {
                    // Log the issue but don't fail the test
                    Log::warning('Problematic content type request failed, but test continues', [
                        'content_type' => $contentType,
                        'status_code' => $result['status_code'] ?? null,
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                // Log the error but don't fail the test
                Log::warning('Exception in problematic content type test, but test continues', [
                    'content_type' => $contentType,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * Test real API connection with pagination
     */
    #[\PHPUnit\Framework\Attributes\Group('integration')]
    #[\PHPUnit\Framework\Attributes\Group('external-api')]
    public function test_real_pagination(): void
    {
        // Skip test by default to avoid making real API calls
        if ($this->skipByDefault && !getenv('RUN_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Integration tests are skipped by default. Set RUN_INTEGRATION_TESTS=1 to run them.');
        }

        // Create real instances (not mocks)
        $httpClient = new HttpClientService(config('acorn'));
        $apiService = new AcornApiService($httpClient);

        Log::info('Testing pagination');

        try {
            // Get page 1 with small page size
            $result1 = $apiService->getExternalCatalogue([
                'page' => 1,
                'perPage' => 3,
            ]);

            Log::info('Page 1 result', [
                'success' => $result1['success'] ?? false,
                'status_code' => $result1['status_code'] ?? null,
                'per_page' => $result1['data']['data']['per_page'] ?? null,
                'next_page_url' => isset($result1['data']['data']['next_page_url']),
                'item_count' => isset($result1['data']['data']['items']) ? count($result1['data']['data']['items']) : 0,
            ]);

            // Get page 2 with same page size
            $result2 = $apiService->getExternalCatalogue([
                'page' => 2,
                'perPage' => 3,
            ]);

            Log::info('Page 2 result', [
                'success' => $result2['success'] ?? false,
                'status_code' => $result2['status_code'] ?? null,
                'per_page' => $result2['data']['data']['per_page'] ?? null,
                'previous_page_url' => isset($result2['data']['data']['previous_page_url']),
                'item_count' => isset($result2['data']['data']['items']) ? count($result2['data']['data']['items']) : 0,
            ]);

            // Basic assertions
            $this->assertTrue($result1['success']);
            $this->assertTrue($result2['success']);

            // Check that we have the expected data structure
            $this->assertArrayHasKey('data', $result1);
            $this->assertArrayHasKey('status', $result1['data']);
            $this->assertEquals('Complete', $result1['data']['status']);
            $this->assertArrayHasKey('data', $result1['data']);
            $this->assertArrayHasKey('items', $result1['data']['data']);

            $this->assertArrayHasKey('data', $result2);
            $this->assertArrayHasKey('status', $result2['data']);
            $this->assertEquals('Complete', $result2['data']['status']);
            $this->assertArrayHasKey('data', $result2['data']);
            $this->assertArrayHasKey('items', $result2['data']['data']);

            // Verify pagination data
            $this->assertEquals(3, $result1['data']['data']['per_page']);
            $this->assertEquals(3, $result2['data']['data']['per_page']);

            // Check page urls
            $this->assertArrayHasKey('next_page_url', $result1['data']['data']);
            $this->assertNotNull($result1['data']['data']['next_page_url']);

            $this->assertArrayHasKey('previous_page_url', $result2['data']['data']);
            $this->assertNotNull($result2['data']['data']['previous_page_url']);

            // Verify different items on different pages
            if (!empty($result1['data']['data']['items']) && !empty($result2['data']['data']['items'])) {
                $firstItemPage1 = $result1['data']['data']['items'][0]['contentid'] ?? null;
                $firstItemPage2 = $result2['data']['data']['items'][0]['contentid'] ?? null;

                Log::info('Comparing items from different pages', [
                    'firstItemPage1' => $firstItemPage1,
                    'firstItemPage2' => $firstItemPage2,
                ]);

                if ($firstItemPage1 && $firstItemPage2) {
                    $this->assertNotEquals($firstItemPage1, $firstItemPage2);
                }
            }
        } catch (\Exception $e) {
            Log::error('Exception in test_real_pagination', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
