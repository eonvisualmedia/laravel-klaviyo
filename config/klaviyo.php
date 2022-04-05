<?php

return [

    /*
     * Enable or disable Klaviyo script rendering and server-side jobs.
     * Useful for local development.
     */
    'enabled' => (bool) env('KLAVIYO_ENABLED', true),

    'endpoint' => env('KLAVIYO_ENDPOINT', 'https://a.klaviyo.com/api/'),

    /**
     * The queue on which jobs will be processed.
     */
    'queue' => env('KLAVIYO_QUEUE', 'klaviyo'),

    'private_api_key' => env('KLAVIYO_PRIVATE_API_KEY', ''),

    'public_api_key' => env('KLAVIYO_PUBLIC_API_KEY', ''),

    'identity_key_name' => env('KLAVIYO_IDENTITY_KEY_NAME'),

];
