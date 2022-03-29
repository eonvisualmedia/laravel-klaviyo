<?php

namespace EonVisualMedia\LaravelKlaviyo\Contracts;

interface TrackEventInterface
{
    public function getEvent(): string;

    public function setEvent(string $event);

    public function getProperties(): array|null;

    public function setProperties(array|null $properties);

    public function getIdentity(): KlaviyoIdentity|string|null;

    public function setIdentity(KlaviyoIdentity|string|null $identity);

    public function getTimestamp(): int|null;

    public function setTimestamp(int|null $timestamp);
}
