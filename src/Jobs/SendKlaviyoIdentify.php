<?php

namespace EonVisualMedia\LaravelKlaviyo\Jobs;

use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendKlaviyoIdentify implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(protected array $attributes)
    {
        $this->onQueue(config('klaviyo.queue'));
    }

    public function handle(KlaviyoClient $client)
    {
        $client
            ->post('profile-import', [
                'data' => [
                    'type'       => 'profile',
                    'attributes' => klaviyo_client_to_server_profile($this->attributes)
                ]
            ])
            ->throw();
    }
}
