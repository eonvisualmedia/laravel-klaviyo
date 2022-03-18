<?php

return [

    /**
     * Name of the queue to run Klaviyo calls on
     */
    'queue' => env('KLAVIYO_QUEUE', 'klaviyo'),

    'private_api_key' => env('KLAVIYO_PRIVATE_API_KEY'),

    'public_api_key' => env('KLAVIYO_PUBLIC_API_KEY'),

];
