<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use Carbon\Carbon;
use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

class SendKlaviyoTrack
{
    use Dispatchable;
    use Queueable;

    /**
     * Unix timestamp of when the event occurred.
     *
     * @link https://php.net/manual/en/datetime.gettimestamp.php
     */
    protected int $timestamp;

    public function __construct(protected string $event, protected ?array $properties, protected array $identity, int $timestamp = null)
    {
        $this->onQueue(config('klaviyo.queue'));
        $this->timestamp = $timestamp ?? Carbon::now()->getTimestamp();
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return array|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @return array
     */
    public function getIdentity(): array
    {
        return $this->identity;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function handle(KlaviyoClient $client)
    {
        $payload = [
            'token'               => $client->getPublicKey(),
            'event'               => $this->event,
            'customer_properties' => $this->identity,
            'time'                => $this->timestamp,
        ];

        if (null !== $this->properties) {
            $payload['properties'] = $this->properties;
        }

        $client->request()->post('track', $payload)->throw();
    }
}
