<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\Contracts\TrackEventInterface;
use EonVisualMedia\LaravelKlaviyo\Contracts\ViewedProduct;
use EonVisualMedia\LaravelKlaviyo\Exceptions\KlaviyoException;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoIdentify;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoTrack;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

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
     * Attributes used for the identification of an Identify Profile.
     *
     * @var string[]
     */
    protected array $identifyAttributes = [
        '$email',
        '$id',
        '$phone_number',
        '$exchange_id',
    ];

    protected Collection $pushCollection;

    /**
     * @var bool
     */
    protected bool $enabled = true;

    /**
     * @param  string  $privateKey
     * @param  string  $publicKey
     */
    public function __construct(protected string $privateKey, protected string $publicKey)
    {
        $this->pushCollection = new Collection();
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

    /**
     * Check whether Klaviyo script rendering and server-side jobs is enabled.
     *
     * @return bool
     */
    public function isEnabled()
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
     * @param  TrackEventInterface  ...$events
     * @return void
     */
    public function track(TrackEventInterface ...$events)
    {
        if (! $this->isEnabled()) {
            return;
        }

        $events = collect($events)
            ->map(function (TrackEventInterface $event) {
                $identity = $this->resolveIdentity($event->getIdentity());

                return $event->setIdentity($identity);
            })
            ->filter();

        if ($events->isNotEmpty()) {
            dispatch(new SendKlaviyoTrack(...$events->all()));
        }
    }

    /**
     * Submit a server-side identify event to Klaviyo.
     *
     * @param  KlaviyoIdentity|string|array|null  $identity
     * @return void
     *
     * @throws KlaviyoException
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
     * @param  KlaviyoIdentity|string|array|null  $identity
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function resolveIdentity(KlaviyoIdentity|string|array $identity = null): array
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
        return json_decode(base64_decode(Cookie::get('__kla_id')), true) ?? [];
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
            return ['$exchange_id' => $value];
        } elseif (! empty($value = Arr::get($this->getDecodedCookie(), '$email'))) {
            return ['$email' => $value];
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
     * @param  ViewedProduct  $product
     * @return void
     *
     * @throws KlaviyoException
     */
    public function pushViewed(ViewedProduct $product)
    {
        $item = $product->getViewedProductProperties();
        $this->push('track', 'Viewed Product', $item);
        $this->push('trackViewedItem', $item);
    }
}
