<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

class SendIdentify
{
    use Dispatchable;
    use Queueable;

    public function __construct(protected array $identity)
    {
        $this->queue = config('klaviyo.queue');
    }

    public function handle(KlaviyoClient $client)
    {
        $client->request()->asForm()->post('identify', [
            'data' => json_encode([
                'token'      => $client->getPublicKey(),
                'properties' => $this->identity,
            ]),
        ])->throw();
    }
}
