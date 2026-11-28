<?php

/*
|--------------------------------------------------------------------------
| CORS Configuration
|--------------------------------------------------------------------------
|
| Allowlisted origins for the public API only. The versioned /api/v1/*
| namespace (and nothing else) is callable cross-origin, and only from the
| origins below. Same-origin requests are unaffected.
|
*/

return [

    // Only the versioned API is CORS-enabled.
    'paths' => ['api/v1/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    // Allowed origins: the canonical site and local dev.
    'allowed_origins' => [
        'https://cms.rosettedevelopment.com',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
    ],

    // Allowed headers for the public read endpoints and the API-key write
    // endpoints.
    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept', 'X-API-Key'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
