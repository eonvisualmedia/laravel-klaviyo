<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Jobs\SendTrackEvent;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Macroable;

class KlaviyoClient
{
    use Macroable;

    protected string $baseUri = "https://a.klaviyo.com/api/";

    /**
     * @param string $privateKey
     * @param string $publicKey
     */
    public function __construct(protected string $privateKey, protected string $publicKey)
    {
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function request(): PendingRequest
    {
        return Http::withOptions([
            'base_uri' => $this->baseUri
        ])->withHeaders([
            'Accept' => 'text/html'
        ]);
    }

    public function track(string $event, array $properties = [], array $identity = [])
    {
        dispatch(new SendTrackEvent($event, $properties, empty($identity) ? $this->resolveIdentity() : $identity));
    }

    private function resolveIdentity(): ?array
    {
        $user = Auth::user();

        if ($user) {
            if (isset($user->email)) {
                return ['$email' => $user->email];
            }
        }

        return null;
    }
}
