<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ContentApiTest extends TestCase
{
    /**
     * Test content catalogue API endpoint
     */
    public function test_content_catalogue_endpoint(): void
    {
        // Mock the external API response
        Http::fake([
            'staging.acornlms.com/*' => Http::response([
                'success' => true,
                'data' => [
                    'status' => 'Complete',
                    'data' => [
                        'items' => [
                            [
                                'contentid' => 1,
                                'fullname' => 'Test Course',
                                'summary' => 'Test Course Summary',
                                'imageurl' => 'https://example.com/image.jpg',
                                'contenttype' => 'course',
                                'url' => 'https://example.com/course/1',
                                'cost' => 0,
                                'duration' => '2 hours',
                                'timecreated' => '2023-01-01',
                                'timemodified' => '2023-01-02',
                                'contentstatus' => 'active',
                                'topics' => ['topic 1', 'topic 2'],
                                'competencies' => ['comp 1', 'comp 2'],
                            ],
                            [
                                'contentid' => 2,
                                'fullname' => 'Test Live Learning',
                                'summary' => 'Test Live Learning Summary',
                                'imageurl' => 'https://example.com/image2.jpg',
                                'contenttype' => 'live learning',
                                'url' => 'https://example.com/livelearning/2',
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
                        ],
                        'total_items' => 2,
                        'current_page' => 1,
                        'per_page' => 10,
                        'total_pages' => 1,
                    ]
                ],
                'status_code' => 200,
            ], 200),
        ]);

        // Make the API request
        $response = $this->getJson('/api/v1/content');

        // Just check basic response structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'items',
                'metadata',
            ]);
    }

    /**
     * Test content item API endpoint
     */
    public function test_content_item_endpoint(): void
    {
        // Mock the external API response
        Http::fake([
            'staging.acornlms.com/*' => Http::response([
                'success' => true,
                'data' => [
                    'status' => 'Complete',
                    'data' => [
                        'contentid' => 1,
                        'fullname' => 'Test Course',
                        'summary' => 'Test Course Summary',
                        'imageurl' => 'https://example.com/image.jpg',
                        'contenttype' => 'course',
                        'url' => 'https://example.com/course/1',
                        'cost' => 0,
                        'duration' => '2 hours',
                        'timecreated' => '2023-01-01',
                        'timemodified' => '2023-01-02',
                        'contentstatus' => 'active',
                        'topics' => ['topic 1', 'topic 2'],
                        'competencies' => ['comp 1', 'comp 2'],
                    ]
                ],
                'status_code' => 200,
            ], 200),
        ]);

        $response = $this->getJson('/api/v1/content/1');
        
        // DEBUG: Check for API response error
        if ($response->getStatusCode() !== 200) {
            dump('API Error Response: ' . $response->getContent());
        }
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'item' => [
                    'id',
                    'fullname',
                    'summary',
                    'image',
                    'contentType',
                    'url',
                    'cost',
                    'duration',
                    'timeCreated',
                    'timeModified',
                    'contentStatus',
                ],
            ]);
    }
}
