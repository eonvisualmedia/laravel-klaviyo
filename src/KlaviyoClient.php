<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendIdentify;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendTrackEvent;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Macroable;

class KlaviyoClient
{
    use Macroable;

    protected string $baseUri = 'https://a.klaviyo.com/api/';
    protected string $identifierKey = '$email';

    /**
     * @param  string  $privateKey
     * @param  string  $publicKey
     */
    public function __construct(protected string $privateKey, protected string $publicKey)
    {
    }

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @param  string  $baseUri
     */
    public function setBaseUri(string $baseUri): void
    {
        $this->baseUri = $baseUri;
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

    public function getIdentifierKey(): string
    {
        return $this->identifierKey;
    }

    /**
     * @param  string  $identifierKey
     */
    public function setIdentifierKey(string $identifierKey): void
    {
        $this->identifierKey = $identifierKey;
    }

    public function request(): PendingRequest
    {
        return Http::withOptions([
            'base_uri' => $this->baseUri,
        ])->withHeaders([
            'Accept' => 'text/html',
        ]);
    }

    /**
     * @param  string  $event
     * @param  array  $properties
     * @param  KlaviyoIdentity|string|null  $identity
     * @return void
     */
    public function track(string $event, array $properties = [], KlaviyoIdentity|string $identity = null)
    {
        $identity = $this->resolveIdentity($identity);
        if (!empty($identity)) {
            dispatch(new SendTrackEvent($event, $properties, $identity));
        }
    }

    /**
     * @param  KlaviyoIdentity|string|null  $identity
     * @return void
     */
    public function identify(KlaviyoIdentity|string $identity = null)
    {
        $identity = $this->resolveIdentity($identity);
        if (!empty($identity)) {
            dispatch(new SendIdentify($identity));
        }
    }

    /**
     * @param  KlaviyoIdentity|string|null  $identity
     * @return array|null
     */
    private function resolveIdentity(KlaviyoIdentity|string $identity = null): ?array
    {
        $identity = $identity ?? Auth::user();

        if ($identity instanceof KlaviyoIdentity) {
            return $identity->getKlaviyoIdentity();
        } elseif (is_string($identity)) {
            return [$this->getIdentifierKey() => $identity];
        } else {
            return null;
        }
    }
}
