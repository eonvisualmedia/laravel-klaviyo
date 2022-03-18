<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use Carbon\Carbon;
use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTrackEvent
{
    use Dispatchable;
    use Queueable;

    /**
     * Unix timestamp of when the event occurred.
     * @link https://php.net/manual/en/datetime.gettimestamp.php
     */
    protected int $timestamp;

    public function __construct(protected string $event, protected ?array $properties, protected array $identity)
    {
        $this->queue = config('klaviyo.queue');
        $this->timestamp = Carbon::now()->getTimestamp();
    }

    public function handle(KlaviyoClient $client)
    {
        $client->request()->asForm()->post('track', [
            'data' => json_encode([
                'token'               => $client->getPublicKey(),
                'event'               => $this->event,
                'customer_properties' => $this->identity,
                'properties'          => $this->properties,
                'time'                => $this->timestamp
            ])
        ])->throw();
    }
}
