<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Encryption Keys
    |--------------------------------------------------------------------------
    */

    'keys' => [
        1 => env('APP_ENCRYPTION_KEY_V1'), // Reads from .env
        //2 => env('APP_ENCRYPTION_KEY_V2'),
        // Add more versions as needed
    ],

    // Define which key version is currently active for NEW encryptions
    'current_version' => (int) env('CURRENT_ENCRYPTION_KEY_VERSION', 1), // Reads from .env, defaults to 1
];