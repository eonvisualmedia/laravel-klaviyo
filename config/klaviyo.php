<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Klaviyo Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | The queue on which jobs will be processed.
    |
    */

    'queue' => env('KLAVIYO_QUEUE', 'klaviyo'),

    'private_api_key' => env('KLAVIYO_PRIVATE_API_KEY'),

    'public_api_key' => env('KLAVIYO_PUBLIC_API_KEY'),

    'identity_key_name' => env('KLAVIYO_IDENTITY_KEY_NAME'),

];
