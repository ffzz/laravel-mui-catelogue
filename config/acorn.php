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
        'ttl' => env('ACORN_CACHE_TTL', 60 * 15), // 15 minutes
    ],

    'retry' => [
        'times' => env('ACORN_RETRY_TIMES', 3),
        'sleep' => env('ACORN_RETRY_SLEEP', 1000), // milliseconds
    ],
];
