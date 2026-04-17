<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('ALTCHA_ENABLED', false),

    'hmac_secret' => env('ALTCHA_HMAC_SECRET'),

    'hmac_key_secret' => env('ALTCHA_HMAC_KEY_SECRET'),

    'algorithm' => env('ALTCHA_ALGORITHM', 'pbkdf2'),

    'cost' => (int) env('ALTCHA_COST', 10000),

    'expires' => (int) env('ALTCHA_EXPIRES', 300),

    'memory_cost' => env('ALTCHA_MEMORY_COST') !== null ? (int) env('ALTCHA_MEMORY_COST') : null,

    'parallelism' => env('ALTCHA_PARALLELISM') !== null ? (int) env('ALTCHA_PARALLELISM') : null,

    'cache_store' => env('ALTCHA_CACHE_STORE'),

    'field' => env('ALTCHA_FIELD', 'altcha'),

    'route' => [
        'enabled' => (bool) env('ALTCHA_ROUTE_ENABLED', true),
        'path' => env('ALTCHA_ROUTE_PATH', 'altcha'),
        'name' => env('ALTCHA_ROUTE_NAME', 'altcha.challenge'),
        'prefix' => env('ALTCHA_ROUTE_PREFIX', ''),
        'domain' => env('ALTCHA_ROUTE_DOMAIN'),
        'middleware' => ['web'],
    ],
];
