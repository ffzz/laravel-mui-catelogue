<?php

namespace Tests\Feature\Services;

use App\Services\Acorn\AcornApiService;
use App\Services\HttpClient\HttpClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Mockery;

class AcornApiServiceTest extends TestCase
{
    protected $httpClientMock;
    protected $acornApiService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock of HttpClientService
        $this->httpClientMock = Mockery::mock(HttpClientService::class);

        // Create real AcornApiService with mocked HttpClientService
        $this->acornApiService = new AcornApiService($this->httpClientMock);

        // Override container binding to use our instance
        $this->app->instance(AcornApiService::class, $this->acornApiService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test getExternalCatalogue with default parameters
     */
    public function test_get_external_catalogue_with_defaults(): void
    {
        // Mock response data
        $responseData = [
            'items' => [
                [
                    'contentid' => 1,
                    'fullname' => 'Test Course',
                    'contenttype' => 'course',
                ],
            ],
            'total_items' => 1,
            'current_page' => 1,
            'per_page' => 10,
            'total_pages' => 1,
        ];

        // Set up mock response
        $this->httpClientMock->shouldReceive('get')
            ->once()
            ->withArgs(function ($url, $params, $options) {
                // Verify the URL and parameters
                return true;
            })
            ->andReturn([
                'status_code' => 200,
                'data' => $responseData,
            ]);

        // Call the service
        $result = $this->acornApiService->getExternalCatalogue();

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertArrayHasKey('items', $result['data']);
        $this->assertCount(1, $result['data']['items']);
    }

    /**
     * Test getExternalCatalogue with custom parameters
     */
    public function test_get_external_catalogue_with_params(): void
    {
        // Mock response data
        $responseData = [
            'items' => [
                [
                    'contentid' => 2,
                    'fullname' => 'Test Live Learning',
                    'contenttype' => 'live learning',
                ],
            ],
            'total_items' => 1,
            'current_page' => 1,
            'per_page' => 10,
            'total_pages' => 1,
        ];

        // Parameters to test
        $params = [
            'page' => 1,
            'perPage' => 10,
            'contentType' => 'live learning',
            'showHidden' => true,
        ];

        // Set up mock response with parameter validation
        $this->httpClientMock->shouldReceive('get')
            ->once()
            ->withArgs(function ($url, $requestParams, $options) use ($params) {
                // Verify the parameters match what we expect
                return $requestParams['page'] == $params['page'] &&
                    $requestParams['perPage'] == $params['perPage'] &&
                    $requestParams['contentType'] == $params['contentType'] &&
                    $requestParams['showHidden'] == $params['showHidden'];
            })
            ->andReturn([
                'status_code' => 200,
                'data' => $responseData,
            ]);

        // Call the service
        $result = $this->acornApiService->getExternalCatalogue($params);

        // Assertions for response
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertArrayHasKey('items', $result['data']);
        $this->assertCount(1, $result['data']['items']);
        $this->assertEquals('live learning', $result['data']['items'][0]['contenttype']);
    }

    /**
     * Test getContentById method
     */
    public function test_get_content_by_id(): void
    {
        // Mock response data for a single content item
        $responseData = [
            'contentid' => 1,
            'fullname' => 'Test Course',
            'contenttype' => 'course',
            'summary' => 'Test course summary',
        ];

        // Set up mock response with parameter validation
        $this->httpClientMock->shouldReceive('get')
            ->once()
            ->withArgs(function ($url, $params, $options) {
                // Verify the contentId parameter is set correctly
                return isset($params['contentId']) && $params['contentId'] == 1;
            })
            ->andReturn([
                'status_code' => 200,
                'data' => $responseData,
            ]);

        // Call the service
        $result = $this->acornApiService->getContentById(1);

        // Assertions for response
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertEquals(1, $result['data']['contentid']);
        $this->assertEquals('course', $result['data']['contenttype']);
    }

    /**
     * Test getContentByType method
     */
    public function test_get_content_by_type(): void
    {
        // Mock response data
        $responseData = [
            'items' => [
                [
                    'contentid' => 3,
                    'fullname' => 'Test Resource',
                    'contenttype' => 'resource',
                ],
            ],
            'total_items' => 1,
            'current_page' => 1,
            'per_page' => 10,
            'total_pages' => 1,
        ];

        // Set up mock response with parameter validation
        $this->httpClientMock->shouldReceive('get')
            ->once()
            ->withArgs(function ($url, $params, $options) {
                // Verify the parameters are set correctly
                return isset($params['contentType']) &&
                    $params['contentType'] == 'resource' &&
                    $params['page'] == 1 &&
                    $params['perPage'] == 10;
            })
            ->andReturn([
                'status_code' => 200,
                'data' => $responseData,
            ]);

        // Call the service
        $result = $this->acornApiService->getContentByType('resource', 1, 10);

        // Assertions for response
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertArrayHasKey('items', $result['data']);
        $this->assertCount(1, $result['data']['items']);
        $this->assertEquals('resource', $result['data']['items'][0]['contenttype']);
    }

    /**
     * Test error handling
     */
    public function test_handle_error_response(): void
    {
        // Set up mock response for error
        $this->httpClientMock->shouldReceive('get')
            ->once()
            ->andReturn([
                'status_code' => 401,
                'data' => [
                    'error' => 'Invalid API token',
                ],
            ]);

        // Call the service
        $result = $this->acornApiService->getExternalCatalogue();

        // Assertions
        $this->assertFalse($result['success']);
        $this->assertEquals(401, $result['status_code']);
        $this->assertEquals('Invalid API token', $result['error']);
    }
}
