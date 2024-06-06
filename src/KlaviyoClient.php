<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\Contracts\ViewedProduct;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoIdentify;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoTrack;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

/**
 * @method \Illuminate\Http\Client\Response delete(string $url, array $data = [])
 * @method \Illuminate\Http\Client\Response get(string $url, array|string|null $query = null)
 * @method \Illuminate\Http\Client\Response head(string $url, array|string|null $query = null)
 * @method \Illuminate\Http\Client\Response patch(string $url, array $data = [])
 * @method \Illuminate\Http\Client\Response post(string $url, array $data = [])
 * @method \Illuminate\Http\Client\Response put(string $url, array $data = [])
 * @method \Illuminate\Http\Client\Response send(string $method, string $url, array $options = [])
 * @method \Illuminate\Http\Client\PendingRequest async(bool $async = true)
 * @method array pool(callable $callback)
 **/
class KlaviyoClient
{
    use ForwardsCalls, Macroable {
        __call as macroCall;
    }

    /**
     * @see https://developers.klaviyo.com/en/reference/create_profile
     */
    const SERVER_PROFILE_ATTRIBUTES = [
        'email',
        'phone_number',
        'external_id',
        'anonymous_id',
        '_kx',
        'first_name',
        'last_name',
        'organization',
        'title',
        'image',
        'location',
        'properties',
    ];

    /**
     * API Endpoint.
     *
     * @var string
     */
    protected string $endpoint;

    /**
     * Private API Key.
     *
     * @var string|mixed
     */
    protected string $privateKey;

    /**
     * Public API Key.
     *
     * @var string|mixed
     */
    protected string $publicKey;

    /**
     * Klaviyo API revision to use.
     *
     * @var string|mixed
     */
    protected string $apiVersion;

    /**
     * The key for the identity.
     *
     * @var string
     */
    protected string $identityKeyName;

    /**
     * Attributes used for the identification of an Identify Profile.
     *
     * @var string[]
     */
    protected array $identifyAttributes = [
        'email',
        'id',
        'phone_number',
        '_kx',
    ];

    protected Collection $pushCollection;

    /**
     * @var bool
     */
    protected bool $enabled = true;

    public function __construct(array $config)
    {
        $this->endpoint = $config['endpoint'] ?? '';
        $this->privateKey = $config['private_api_key'] ?: throw new InvalidArgumentException('Invalid private api key');
        $this->publicKey = $config['public_api_key'] ?: throw new InvalidArgumentException('Invalid public api key');
        $this->apiVersion = $config['api_version'] ?: throw new InvalidArgumentException('Invalid API Version');
        $this->identityKeyName = $config['identity_key_name'] ?: throw new InvalidArgumentException('Invalid default identity key name');
        $this->enabled = $config['enabled'] ?? true;

        $this->pushCollection = new Collection();
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
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
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
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
     * Check whether Klaviyo script rendering and server-side jobs is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable Klaviyo script rendering and server-side jobs.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable Klaviyo script rendering and server-side jobs.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Submit a server-side track event to Klaviyo.
     *
     * @param TrackEvent ...$events
     * @return void
     */
    public function track(TrackEvent ...$events)
    {
        if (! $this->isEnabled()) {
            return;
        }

        $events = collect($events)
            ->reject(fn($event) => empty($event->identity));

        if ($events->isNotEmpty()) {
            dispatch(new SendKlaviyoTrack(...$events->all()));
        }
    }

    /**
     * Submit a server-side identify event to Klaviyo.
     *
     * @param KlaviyoIdentity|string|array|null $identity
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function identify(KlaviyoIdentity|string|array $identity = null)
    {
        if (! $this->isEnabled()) {
            return;
        }

        $identity = $this->resolveIdentity($identity ?? Auth::user());
        dispatch(new SendKlaviyoIdentify($identity));
    }

    /**
     * Resolve identity or profile of user
     *
     * @throws InvalidArgumentException
     */
    public function resolveIdentity(KlaviyoIdentity|string|array|null $identity = null): ?array
    {
        if ($identity === null && $this->isIdentified()) {
            return $this->getIdentity();
        }

        $identity = with($identity ?? Auth::user(), function ($value) {
            if ($value instanceof KlaviyoIdentity) {
                return $value->getKlaviyoIdentity();
            } elseif (is_string($value)) {
                return [$this->getIdentityKeyName() => $value];
            } elseif (is_array($value)) {
                return $value;
            } else {
                return null;
            }
        });

        if (empty(array_intersect_key($identity ?? [], array_flip($this->identifyAttributes)))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Identify requires one of the following fields: %s',
                    implode(', ', $this->identifyAttributes)
                )
            );
        }

        return $identity;
    }

    /**
     * Decode the __kla_id cookie.
     *
     * @return array
     */
    public function getDecodedCookie(): array
    {
        return json_decode(base64_decode(Cookie::get('__kla_id', '')), true) ?? [];
    }

    /**
     * Does the \Illuminate\Http\Request cookie contain an $exchange_id?
     *
     * @return bool
     */
    public function isIdentified(): bool
    {
        return $this->getIdentity() !== null;
    }

    /**
     * Retrieve the $exchange_id from cookie.
     *
     * @return array|null
     */
    public function getIdentity(): ?array
    {
        if (! empty($value = Arr::get($this->getDecodedCookie(), '$exchange_id'))) {
            return ['_kx' => $value];
        } elseif (! empty($value = Arr::get($this->getDecodedCookie(), '$email'))) {
            return ['email' => $value];
        } else {
            return null;
        }
    }

    /**
     * @return Collection
     */
    public function getPushCollection(): Collection
    {
        return $this->pushCollection;
    }

    /**
     * Push an event to be rendered by the client to the beginning of the collection.
     *
     * @throws InvalidArgumentException
     */
    public function prepend(...$values)
    {
        if (count($values) === 0) {
            throw new InvalidArgumentException('Not enough arguments for prepend.');
        } elseif (count($values) > 3) {
            throw new InvalidArgumentException('Too many arguments for prepend.');
        }

        $this->pushCollection->prepend($values);
    }

    /**
     * Push an event to be rendered by the client.
     *
     * @throws InvalidArgumentException
     */
    public function push(...$values)
    {
        if (count($values) === 0) {
            throw new InvalidArgumentException('Not enough arguments for push.');
        } elseif (count($values) > 3) {
            throw new InvalidArgumentException('Too many arguments for push.');
        }

        $this->pushCollection->push($values);
    }

    /**
     * Push a viewed product event to be rendered by the client.
     *
     * @param ViewedProduct $product
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function pushViewed(ViewedProduct $product)
    {
        $item = $product->getViewedProductProperties();
        $this->push('track', 'Viewed Product', $item);
        $this->push('trackViewedItem', $item);
    }

    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->getEndpoint())
            ->acceptJson()
            ->asJson()
            ->withToken($this->privateKey, 'Klaviyo-API-Key')
            ->withHeaders([
                'revision' => $this->getApiVersion()
            ]);
    }

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->forwardCallTo($this->client(), $method, $parameters);
    }
}
