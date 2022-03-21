<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoIdentify;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoTrack;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Macroable;

class KlaviyoClient
{
    use Macroable;

    protected string $baseUri = 'https://a.klaviyo.com/api/';

    /**
     * The key for the identity.
     *
     * @var string
     */
    protected string $identityKeyName = '$email';

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
     * @return KlaviyoClient
     */
    public function setBaseUri(string $baseUri): KlaviyoClient
    {
        $this->baseUri = $baseUri;

        return $this;
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

    /**
     * The key for the identity.
     *
     * @return string
     */
    public function getIdentityKeyName(): string
    {
        return $this->identityKeyName;
    }

    /**
     * @param  string  $identityKeyName
     * @return KlaviyoClient
     */
    public function setIdentityKeyName(string $identityKeyName): KlaviyoClient
    {
        $this->identityKeyName = $identityKeyName;

        return $this;
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
        if (! empty($identity)) {
            dispatch(new SendKlaviyoTrack($event, $properties, $identity));
        }
    }

    /**
     * @param  KlaviyoIdentity|string|null  $identity
     * @return void
     */
    public function identify(KlaviyoIdentity|string $identity = null)
    {
        $identity = $this->resolveIdentity($identity);
        if (! empty($identity)) {
            dispatch(new SendKlaviyoIdentify($identity));
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
            return [$this->getIdentityKeyName() => $identity];
        } else {
            return null;
        }
    }
}
