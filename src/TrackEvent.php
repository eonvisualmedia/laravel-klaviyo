<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\Contracts\TrackEventInterface;
use Illuminate\Support\Carbon;

class TrackEvent implements TrackEventInterface
{
    protected string $event;
    protected array|null $properties;
    protected string|KlaviyoIdentity|array|null $identity;
    protected Carbon|null $timestamp;

    public static function make(
        string $event,
        array $properties = null,
        KlaviyoIdentity|string|array $identity = null,
        Carbon $timestamp = null
    ): TrackEvent {
        return (new static())
            ->setEvent($event)
            ->setProperties($properties)
            ->setIdentity($identity)
            ->setTimestamp($timestamp);
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param  string  $event
     * @return TrackEvent
     */
    public function setEvent(string $event): TrackEvent
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getProperties(): array|null
    {
        return $this->properties;
    }

    /**
     * @param  array|null  $properties
     * @return TrackEvent
     */
    public function setProperties(array|null $properties): TrackEvent
    {
        $this->properties = $properties;

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
     * @param  KlaviyoIdentity|string|array|null  $identity
     * @return TrackEvent
     */
    public function setIdentity(KlaviyoIdentity|string|array|null $identity): TrackEvent
    {
        $this->identity = $identity;

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
     * @param  Carbon|null  $timestamp
     * @return TrackEvent
     */
    public function setTimestamp(Carbon $timestamp = null): TrackEvent
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
