<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use EonVisualMedia\LaravelKlaviyo\TrackEvent;
use GuzzleHttp\Promise\Each;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Pool;
use Illuminate\Queue\InteractsWithQueue;

class SendKlaviyoTrack implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * @var array|TrackEvent[]
     */
    protected array $events;

    /**
     * @var int number of requests to execute concurrently
     */
    public int $concurrency = 5;

    public function __construct(TrackEvent ...$events)
    {
        $this->onQueue(config('klaviyo.queue'));
        $this->events = $events;
    }

    public function handle(KlaviyoClient $client)
    {
        $requests = function (Pool $pool) use ($client) {
            foreach ($this->events as $event) {
                yield $pool
                    ->acceptJson()
                    ->asJson()
                    ->withToken($client->getPrivateKey(), 'Klaviyo-API-Key')
                    ->withHeaders([
                        'revision' => $client->getApiVersion()
                    ])
                    ->post($client->getEndpoint().'events', [
                        'data' => $event->toPayload()
                    ]);
            }
        };

        $client->pool(function (Pool $pool) use ($requests) {
            Each::ofLimit(
                $requests($pool),
                $this->concurrency,
                fn($response, $index) => $response->throw(),
            )->wait();
        });
    }
}
