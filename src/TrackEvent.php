<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TrackEvent
{
    public string $id;
    public array $identity;
    public Carbon $time;

    public function __construct(
        public string                $metric_name,
        public array                 $payload = [],
        KlaviyoIdentity|string|array $identity = null,
        Carbon                       $time = null
    )
    {
        $this->id = Str::uuid()->toString();
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
        return [
            'type'       => 'event',
            'attributes' => array_merge_recursive([
                'properties' => [],
                'time'       => $this->time->toIso8601String(),
                'unique_id'  => $this->id,
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
                        'attributes' => klaviyo_client_to_server_profile($this->identity)
                    ],
                ],
            ], $this->payload),
        ];
    }
}
