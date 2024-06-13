<?php

use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Support\Arr;

if (! function_exists('klaviyo_client_to_server_profile')) {
    /**
     * Client and server api have different payloads; move unknown attributes to custom properties array
     *
     * @param array $attributes
     * @return array
     */
    function klaviyo_client_to_server_profile(array $attributes): array
    {
        return collect(Arr::dot($attributes))
            ->keyBy(fn($item, $key) => in_array(explode('.', $key)[0], KlaviyoClient::SERVER_PROFILE_ATTRIBUTES) ? $key : "properties.{$key}")
            ->undot()
            ->all();
    }
}
