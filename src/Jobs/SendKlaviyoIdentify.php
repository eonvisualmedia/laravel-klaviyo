<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

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
        $client->request()->post('identify', [
            'token'      => $client->getPublicKey(),
            'properties' => $this->identity,
        ])->throw();
    }
}
