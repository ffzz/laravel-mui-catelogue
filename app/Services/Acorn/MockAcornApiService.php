<?php

namespace App\Services\Acorn;

use Illuminate\Support\Facades\Log;

class MockAcornApiService extends AcornApiService
{
    /**
     * Mock the external catalogue response
     * 
     * @param array $params Query parameters
     * @return array Mock response
     */
    public function getExternalCatalogue(array $params = []): array
    {
        Log::info('Using mock external catalogue', [
            'params' => $params,
        ]);

        // Extract params for filtering
        $page = $params['page'] ?? 1;
        $perPage = $params['perPage'] ?? 10;
        $contentId = $params['contentId'] ?? null;
        $contentType = $params['contentType'] ?? null;

        // Create base mock data
        $allItems = [
            [
                'contentid' => 1,
                'fullname' => 'Test Course',
                'summary' => 'This is a test course for development',
                'imageurl' => 'https://picsum.photos/id/1/800/600',
                'contenttype' => 'course',
                'url' => 'https://example.com/course/1',
                'cost' => 0,
                'duration' => '2 hours',
                'timecreated' => '2023-01-01',
                'timemodified' => '2023-01-02',
                'contentstatus' => 'active',
                'topics' => ['Development', 'Testing'],
                'competencies' => ['Development Skills', 'Testing Skills'],
            ],
            [
                'contentid' => 2,
                'fullname' => 'Test Live Learning',
                'summary' => 'This is a test live learning session for development',
                'imageurl' => 'https://picsum.photos/id/2/800/600',
                'contenttype' => 'live learning',
                'url' => 'https://example.com/live/2',
                'cost' => 100,
                'duration' => '1 day',
                'timecreated' => '2023-01-01',
                'timemodified' => '2023-01-02',
                'contentstatus' => 'active',
                'start_time' => '2023-05-01 09:00:00',
                'end_time' => '2023-05-01 17:00:00',
                'location' => 'Sydney',
                'facilitator' => 'Jane Doe',
                'max_attendees' => 20,
            ],
            [
                'contentid' => 3,
                'fullname' => 'Test Resource',
                'summary' => 'This is a test resource for development',
                'imageurl' => 'https://picsum.photos/id/3/800/600',
                'contenttype' => 'resource',
                'url' => 'https://example.com/resource/3',
                'cost' => 0,
                'duration' => '30 minutes',
                'timecreated' => '2023-01-01',
                'timemodified' => '2023-01-02',
                'contentstatus' => 'active',
                'resource_type' => 'pdf',
                'file_url' => 'https://example.com/files/resource.pdf',
            ],
            [
                'contentid' => 4,
                'fullname' => 'Test Video',
                'summary' => 'This is a test video for development',
                'imageurl' => 'https://picsum.photos/id/4/800/600',
                'contenttype' => 'video',
                'url' => 'https://example.com/video/4',
                'cost' => 0,
                'duration' => '45 minutes',
                'timecreated' => '2023-01-01',
                'timemodified' => '2023-01-02',
                'contentstatus' => 'active',
                'video_url' => 'https://example.com/videos/test.mp4',
                'video_duration' => 2700,
                'video_provider' => 'YouTube',
            ],
            [
                'contentid' => 5,
                'fullname' => 'Test Page',
                'summary' => 'This is a test page for development',
                'imageurl' => 'https://picsum.photos/id/5/800/600',
                'contenttype' => 'page',
                'url' => 'https://example.com/page/5',
                'cost' => 0,
                'duration' => '15 minutes',
                'timecreated' => '2023-01-01',
                'timemodified' => '2023-01-02',
                'contentstatus' => 'active',
                'content' => '<p>This is a test page content with <strong>HTML</strong>.</p>',
            ],
        ];

        // Filter by contentId if specified
        if ($contentId !== null) {
            $items = array_filter($allItems, function ($item) use ($contentId) {
                return $item['contentid'] == $contentId;
            });

            // Return single item if contentId is specified
            if (count($items) > 0) {
                $item = reset($items);
                return [
                    'success' => true,
                    'data' => $item,
                    'status_code' => 200,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Item not found',
                    'status_code' => 404,
                ];
            }
        }

        // Filter by contentType if specified
        if ($contentType !== null) {
            $items = array_filter($allItems, function ($item) use ($contentType) {
                return $item['contenttype'] == $contentType;
            });
        } else {
            $items = $allItems;
        }

        // Convert to indexed array
        $items = array_values($items);

        // Calculate pagination
        $totalItems = count($items);
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($items, $offset, $perPage);

        // Build response data
        $mockData = [
            'items' => $items,
            'total_items' => $totalItems,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'next_page_url' => $page < $totalPages ? "?page=" . ($page + 1) : null,
            'previous_page_url' => $page > 1 ? "?page=" . ($page - 1) : null,
        ];

        return [
            'success' => true,
            'data' => $mockData,
            'status_code' => 200,
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
}
