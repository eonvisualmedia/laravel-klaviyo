<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use Illuminate\Support\Carbon;

class TrackEvent
{
    public array $identity;
    public Carbon $time;

    public function __construct(
        public string                $metric_name,
        public array                 $payload = [],
        KlaviyoIdentity|string|array $identity = null,
        Carbon                       $time = null
    )
    {
        $this->identity = Klaviyo::resolveIdentity($identity);
        $this->time = $time ?? Carbon::now();
    }

    public static function make(
        string                       $metric_name,
        array                        $payload = [],
        KlaviyoIdentity|string|array $identity = null,
        Carbon                       $timestamp = null
    ): TrackEvent
    {
        return new static($metric_name, $payload, $identity, $timestamp);
    }

    public function toPayload(): array
    {
        return array_merge_recursive([
            'properties' => [],
            'time'       => $this->time->toIso8601String(),
            'metric'     => [
                'data' => [
                    'type'       => 'metric',
                    'attributes' => [
                        'name' => $this->metric_name,
                    ]
                ]
            ],
            'profile'    => [
                'data' => [
                    'type'       => 'profile',
                    'attributes' => Klaviyo::clientProfileToServerProfile($this->identity)
                ],
            ],
        ], $this->payload);
    }
}
