<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TrackEvent
{
    public string $id;
    public array $identity;
    public Carbon $timestamp;

    public function __construct(
        public string                $metric_name,
        public array                 $payload = [],
        KlaviyoIdentity|string|array $identity = null,
        Carbon                       $time = null
    )
    {
        $this->id = Str::uuid()->toString();
        $this->identity = Klaviyo::resolveIdentity($identity);
        $this->timestamp = $time ?? Carbon::now();
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

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->metric_name;
    }

    /**
     * @param string $event
     * @return TrackEvent
     */
    public function setEvent(string $event): TrackEvent
    {
        $this->metric_name = $event;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getProperties(): array|null
    {
        return $this->payload;
    }

    /**
     * @param array|null $properties
     * @return TrackEvent
     */
    public function setProperties(array|null $properties): TrackEvent
    {
        $this->payload = $properties;

        return $this;
    }

    /**
     * @return KlaviyoIdentity|string|array|null
     */
    public function getIdentity(): KlaviyoIdentity|string|array|null
    {
        return $this->identity;
    }

    /**
     * @param KlaviyoIdentity|string|array|null $identity
     * @return TrackEvent
     */
    public function setIdentity(KlaviyoIdentity|string|array|null $identity): TrackEvent
    {
        $this->identity = Klaviyo::resolveIdentity($identity);

        return $this;
    }

    /**
     * @return Carbon|null
     */
    public function getTimestamp(): Carbon|null
    {
        return $this->timestamp;
    }

    /**
     * @param Carbon|null $timestamp
     * @return TrackEvent
     * @deprecated $timestamp
     */
    public function setTimestamp(Carbon $timestamp = null): TrackEvent
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function toPayload(): array
    {
        return [
            'type'       => 'event',
            'attributes' => array_merge_recursive([
                'properties' => [],
                'time'       => $this->timestamp->toIso8601String(),
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
