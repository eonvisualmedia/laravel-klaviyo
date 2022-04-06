<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use EonVisualMedia\LaravelKlaviyo\Contracts\TrackEventInterface;
use EonVisualMedia\LaravelKlaviyo\Http\Middleware\TrackAndIdentify;
use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use GuzzleHttp\Promise\EachPromise;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SendKlaviyoTrack implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /**
     * Unix timestamp of when the event occurred.
     *
     * @link https://php.net/manual/en/datetime.gettimestamp.php
     */
    protected int $timestamp;

    protected array $events;

    public function __construct(TrackEventInterface ...$events)
    {
        $this->onQueue(config('klaviyo.queue'));
        $this->timestamp = now()->getTimestamp();
        $this->events = $events;
    }

    public function handle(KlaviyoClient $client)
    {
        $http = Http::baseUrl($client->getEndpoint())->async()->withMiddleware(TrackAndIdentify::middleware());

        $requests = function () use ($http, $client) {
            foreach ($this->events as $event) {
                $payload = [
                    'token' => $client->getPublicKey(),
                    'event' => $event->getEvent(),
                    'customer_properties' => $event->getIdentity(),
                    'time' => $event->getTimestamp()?->getTimestamp() ?? $this->timestamp,
                ];

                if (null !== $event->getProperties()) {
                    $payload['properties'] = $event->getProperties();
                }

                yield $http->post('track', $payload);
            }
        };

        $promise = (new EachPromise($requests(), [
            'concurrency' => 5,
            'fulfilled' => function (Response $response) {
                throw_if($response->failed(), $response->toException());
            },
        ]))->promise();

        $promise->wait();
    }
}
