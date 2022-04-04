<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use EonVisualMedia\LaravelKlaviyo\Http\Middleware\TrackAndIdentify;
use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class SendKlaviyoIdentify
{
    use Dispatchable;
    use Queueable;

    public function __construct(protected array $identity)
    {
        $this->onQueue(config('klaviyo.queue'));
    }

    public function handle(KlaviyoClient $client)
    {
        $http = Http::baseUrl($client->getBaseUri())->withMiddleware(TrackAndIdentify::middleware());

        $http->post('identify', [
            'token' => $client->getPublicKey(),
            'properties' => $this->identity,
        ])->throw();
    }
}
