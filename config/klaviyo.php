<?php

return [

    /*
     * Enable or disable Klaviyo script rendering and server-side jobs.
     * Useful for local development.
     */
    'enabled' => (bool)env('KLAVIYO_ENABLED', true),

    'endpoint' => env('KLAVIYO_ENDPOINT', 'https://a.klaviyo.com/api/'),

    'api_version' => env('KLAVIYO_API_VERSION', '2024-05-15'),

    /**
     * The queue on which jobs will be processed.
     */
    'queue'       => env('KLAVIYO_QUEUE', 'klaviyo'),

    'private_api_key' => env('KLAVIYO_PRIVATE_API_KEY', ''),

    'public_api_key' => env('KLAVIYO_PUBLIC_API_KEY', ''),

    'identity_key_name' => env('KLAVIYO_IDENTITY_KEY_NAME', 'email'),

    /**
     * Key under which to flash data to the session.
     */
    'session_key'       => env('KLAVIYO_SESSION_KEY', '_klaviyo'),

    /**
     * Push a klaviyo.identify(...) call after a \Illuminate\Auth\Events\Login event
     */
    'identify_on_login' => env('KLAVIYO_IDENTITY_ON_LOGIN', true),

];
