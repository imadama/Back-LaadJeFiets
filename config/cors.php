<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8000', 'http://laadjefiets.nl', 'http://localhost:5173'], // Allow all origins for development
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
    'allowed_origins_patterns' => ['*'],
];
