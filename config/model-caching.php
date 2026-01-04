<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be prepended to all cache keys. This is useful for
    | multi-tenant applications or when you want to separate cache keys.
    |
    */
    'cache-prefix' => env('MODEL_CACHE_PREFIX', 'model-cache'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable model caching globally. You can also disable it
    | per-query using the disableCache() method.
    |
    */
    'enabled' => env('MODEL_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Use Database Keying
    |--------------------------------------------------------------------------
    |
    | When enabled, cache keys will include the database connection name and
    | database name. This is useful for multi-tenant applications.
    |
    */
    'use-database-keying' => env('MODEL_CACHE_USE_DATABASE_KEYING', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | Specify a custom cache store to use for model caching. If null, the
    | default cache store will be used. This should be a taggable cache
    | store (Redis, Memcached) for best performance.
    |
    */
    'store' => env('MODEL_CACHE_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | Default cache duration in seconds. This can be overridden per model
    | using the cacheTtl property or per-query using cacheFor() method.
    |
    */
    'cache_duration' => env('MODEL_CACHE_DURATION', 3600),
];
