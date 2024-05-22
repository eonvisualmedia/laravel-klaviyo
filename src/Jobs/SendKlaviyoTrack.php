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
                    ->withToken($client->getPrivateKey(), 'Klaviyo-API-Key')
                    ->withHeaders([
                        'revision' => $client->getApiVersion()
                    ])
                    ->post($client->getEndpoint() . 'events', [
                        'data' => [
                            'type'       => 'event',
                            'attributes' => [
                                'properties'     => $event->properties,
                                'time'           => $event->time->toIso8601String(),
                                'value'          => 0,
                                'value_currency' => 'GBP',
                                'unique_id'      => $this->job->uuid(),
                                'metric'         => [
                                    'data' => [
                                        'type'       => 'metric',
                                        'attributes' => [
                                            'name' => $event->metric_name,
                                        ]
                                    ]
                                ],
                                'profile'        => [
                                    'data' => [
                                        'type'       => 'profile',
                                        'attributes' => $event->identity
                                    ],
                                ],
                            ],
                        ]
                    ]);
            }
        };

        $responses = $client->pool(fn(Pool $pool) => [
            Each::ofLimit($requests($pool), 5)
        ]);

        foreach ($responses as $response) {
            $response->throw();
        }
    }
}
