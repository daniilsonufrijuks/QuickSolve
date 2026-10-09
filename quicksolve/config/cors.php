<?php

$origins = array_values(array_unique(array_filter([
    env('APP_URL', 'http://localhost'),
    env('FRONTEND_URL', 'http://localhost:5173'),
])));

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
