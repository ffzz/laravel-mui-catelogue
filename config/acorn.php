<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Acorn API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the Acorn External Catalogue API.
    |
    */

    'api' => [
        'base_url' => env('ACORN_API_BASE_URL', 'https://staging.acornlms.com'),
        'tenancy_id' => env('ACORN_API_TENANCY_ID', '3'),
        'token' => env('ACORN_API_TOKEN', 'WTZ1RHJ3RjdPOW95N0tDT1pvWFNwR2tTQ042ejBKVHVMRUdsTE1PRQ=='),
        'version' => env('ACORN_API_VERSION', '1.1'),
        'per_page' => env('ACORN_API_PER_PAGE', 10),
    ],

    'cache' => [
        'enabled' => env('ACORN_CACHE_ENABLED', true),
        'ttl' => env('ACORN_CACHE_TTL', 60 * 15), // 15 minutes default
        'version' => env('ACORN_CACHE_VERSION', 'v1'), // Cache version for easy invalidation

        // Content type specific cache durations (in seconds)
        'content_types' => [
            'course' => env('ACORN_CACHE_COURSE_TTL', 60 * 15), // 15 minutes
            'live learning' => env('ACORN_CACHE_LIVE_LEARNING_TTL', 60 * 5), // 5 minutes
            'resource' => env('ACORN_CACHE_RESOURCE_TTL', 60 * 30), // 30 minutes
            'video' => env('ACORN_CACHE_VIDEO_TTL', 60 * 30), // 30 minutes
            'page' => env('ACORN_CACHE_PAGE_TTL', 60 * 60), // 1 hour
            'partnered content' => env('ACORN_CACHE_PARTNERED_CONTENT_TTL', 60 * 60), // 1 hour
        ],

        // Background refresh settings
        'background_refresh' => [
            'enabled' => env('ACORN_CACHE_BACKGROUND_REFRESH', true),
            'threshold' => env('ACORN_CACHE_REFRESH_THRESHOLD', 0.8), // Refresh when 80% of TTL has elapsed
        ],
    ],

    'retry' => [
        'times' => env('ACORN_RETRY_TIMES', 3),
        'sleep' => env('ACORN_RETRY_SLEEP', 1000), // milliseconds
    ],
];
