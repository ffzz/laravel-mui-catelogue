<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Class Namespace
    |--------------------------------------------------------------------------
    |
    | This is the namespace where the Data classes are located
    | This configuration is used to find the Data classes when caching data structures
    |
    */
    'data_namespace' => 'App\\Data',

    /*
    |--------------------------------------------------------------------------
    | Data Validation Rules
    |--------------------------------------------------------------------------
    |
    | These rules will be used by the data validator when constructing objects
    | This behaviour can be disabled by setting 'validate_by_default' to false
    |
    */
    'validatable' => [
        'validate_by_default' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | The storage location for cached data structures
    | This path is relative to Laravel's storage_path()
    |
    */
    'cache_path' => 'framework/laravel-data',

    /*
    |--------------------------------------------------------------------------
    | Custom Transformers
    |--------------------------------------------------------------------------
    |
    | Configure the transformers that will be used when transforming data
    | The key should be the class or interface and the value the transformer
    |
    */
    'transformers' => [
        //          
        // such as: \DateTime::class => \App\Data\Transformers\DateTimeTransformer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Include or Exclude Fields
    |--------------------------------------------------------------------------
    |
    | Define the fields that are included by default, or excluded by default.
    | This will affect the result when serialising Data objects to arrays or JSON.
    |
    */
    'features' => [
        'include_and_exclude_properties' => true,
        'cast_and_transform_iterables' => true,
    ],
];
