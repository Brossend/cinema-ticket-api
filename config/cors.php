<?php

declare(strict_types=1);

return [
    'paths' => ['api/*'],

    'allowed_methods' => [
        'GET',
        'POST',
        'DELETE',
        'OPTIONS',
    ],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Content-Type',
        'X-Reservation-Token',
    ],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,
];
