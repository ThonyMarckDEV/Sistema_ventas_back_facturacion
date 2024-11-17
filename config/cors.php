<?php

return [



    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    //'allowed_origins' => ['http://localhost', 'http://127.0.0.1:8000','https://redfish-precise-mentally.ngrok-free.app'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
