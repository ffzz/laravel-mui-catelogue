<?php

namespace App\Services\Acorn;

use App\Services\HttpClient\HttpClientService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AcornApiService
{
    protected HttpClientService $httpClient;
    protected string $baseUrl;
    protected string $tenancyId;
    protected string $apiVersion;
    protected ?string $token;
    protected int $perPage;

    public function __construct(HttpClientService $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = Config::get('acorn.api.base_url');
        $this->tenancyId = Config::get('acorn.api.tenancy_id');
        $this->apiVersion = Config::get('acorn.api.version');
        $this->token = Config::get('acorn.api.token') ?? '';
        $this->perPage = Config::get('acorn.api.per_page');
    }

    /**
     * Get external catalogue with flexible query parameters
     * 
     * @param array $params Optional parameters:
     *  - page (int): Page number for pagination, default 1
     *  - perPage (int): Results per page, default from config
     *  - contentId (int): Filter by content ID
     *  - contentType (string): Filter by content type
     *  - username (string): Username of the user viewing the catalogue
     *  - nameFilter (string): Filter results by fullname
     *  - programId (int): Filter results by program
     *  - categoryId (int): Filter results by category
     *  - showHidden (bool): If true, hidden content will be included
     *  - showDeactivated (bool): If true, deactivated content will be included
     * 
     * @return array Response with success status, data and status code
     */
    public function getExternalCatalogue(array $params = []): array
    {
        // Set default values if not provided
        $params['page'] = $params['page'] ?? 1;
        $params['perPage'] = $params['perPage'] ?? $this->perPage;

        // Build the endpoint URL
        $endpoint = "/local/acorn_coursemanagement/index.php/api/{$this->apiVersion}/external_catalogue/{$this->tenancyId}";

        // Log the request
        Log::info('Fetching external catalogue', [
            'endpoint' => $endpoint,
            'params' => $params,
        ]);

        // Set request options with default empty headers
        $options = [
            'headers' => [],
        ];
        
        // Only add Authorization header if token is not empty
        if (!empty($this->token)) {
            $options['headers']['Authorization'] = "Bearer {$this->token}";
        }

        // Make the API request
        $response = $this->httpClient->get(
            $this->baseUrl . $endpoint,
            $params,
            $options
        );

        Log::info('External catalogue response', [
            'response' => $response,
        ]);

        // Handle errors
        if ($response['status_code'] !== 200) {
            Log::error('Failed to fetch external catalogue', [
                'endpoint' => $endpoint,
                'params' => $params,
                'status_code' => $response['status_code'],
                'data' => $response['data'] ?? null,
            ]);

            return [
                'success' => false,
                'error' => $response['data']['error'] ?? 'Failed to fetch external catalogue',
                'status_code' => $response['status_code'],
            ];
        }

        // Return successful response
        return [
            'success' => true,
            'data' => $response['data'],
            'status_code' => $response['status_code'],
        ];
    }

    /**
     * Convenience method to get a specific content item by ID
     * 
     * @param int $contentId The content ID to retrieve
     * @return array Response with success status, data and status code
     */
    public function getContentById(int $contentId): array
    {
        return $this->getExternalCatalogue([
            'contentId' => $contentId,
        ]);
    }

    /**
     * Convenience method to get content filtered by type
     * 
     * @param string $contentType The content type to filter by
     * @param int $page Page number for pagination
     * @param int|null $perPage Results per page
     * @return array Response with success status, data and status code
     */
    public function getContentByType(string $contentType, int $page = 1, ?int $perPage = null): array
    {
        return $this->getExternalCatalogue([
            'contentType' => $contentType,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
}
