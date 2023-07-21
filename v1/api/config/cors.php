<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*', 'api/*', 'api', 'v1/api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://127.0.0.1:5174',
        'http://127.0.0.1:5173',
        'http://localhost:3003', 
        'http://localhost:3000', 
        'https://gaiko-souba-com.icdl.tokyo',
        'https://gaiko-souba-net.icdl.tokyo',
        'https://icdl.tokyo',
        'http://app-net.localhost:5174', 
        'http://app-com.localhost:5175', 
        'http://app-com.localhost:5174', 
        'http://app-cp.localhost:5173', 
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
