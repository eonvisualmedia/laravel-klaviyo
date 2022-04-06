<?php

namespace EonVisualMedia\LaravelKlaviyo\Contracts;

use Illuminate\Support\Carbon;

interface TrackEventInterface
{
    public function getEvent(): string;

    public function setEvent(string $event): TrackEventInterface;

    public function getProperties(): array|null;

    public function setProperties(array|null $properties): TrackEventInterface;

    public function getIdentity(): KlaviyoIdentity|string|array|null;

    public function setIdentity(KlaviyoIdentity|string|array|null $identity): TrackEventInterface;

    public function getTimestamp(): Carbon|null;

    public function setTimestamp(Carbon|null $timestamp): TrackEventInterface;
}
