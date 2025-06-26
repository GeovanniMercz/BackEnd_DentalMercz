<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'doctor/calendar'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5194'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['*'],

    'max_age' => 0,

    'supports_credentials' => true,

];
