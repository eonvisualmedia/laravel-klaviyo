<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\Contracts\TrackEventInterface;

class TrackEvent implements TrackEventInterface
{
    protected string $event;
    protected array|null $properties;
    protected string|KlaviyoIdentity|null $identity;
    protected int|null $timestamp;

    public static function make(string $event, array $properties = null, KlaviyoIdentity|string $identity = null, int $timestamp = null): TrackEvent
    {
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
     *
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
     *
     * @return TrackEvent
     */
    public function setProperties(array|null $properties): TrackEvent
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return KlaviyoIdentity|string|null
     */
    public function getIdentity(): KlaviyoIdentity|string|null
    {
        return $this->identity;
    }

    /**
     * @param  KlaviyoIdentity|string|null  $identity
     *
     * @return TrackEvent
     */
    public function setIdentity(KlaviyoIdentity|string|null $identity): TrackEvent
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTimestamp(): int|null
    {
        return $this->timestamp;
    }

    /**
     * @param  int|null  $timestamp
     *
     * @return TrackEvent
     */
    public function setTimestamp(int $timestamp = null): TrackEvent
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
