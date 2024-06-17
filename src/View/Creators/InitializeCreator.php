<?php

namespace EonVisualMedia\LaravelKlaviyo\View\Creators;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InitializeCreator
{
    protected string $sessionKey;

    public function __construct(protected KlaviyoClient $client, Config $config)
    {
        $this->sessionKey = $config->get('klaviyo.session_key');
    }

    public function create(View $view)
    {
        if ($this->shouldIdentify()) {
            $user = Auth::user();
            if ($user instanceof KlaviyoIdentity) {
                $this->client->prepend('identify', $user->getKlaviyoIdentity());
            }
        }

        $view
            ->with('enabled', $this->client->isEnabled())
            ->with('publicKey', $this->client->getPublicKey())
            ->with('data', $this->client->getPushCollection());
    }

    protected function shouldIdentify(): bool
    {
        if (session()->has($this->sessionKey.'.identify')) {
            return true;
        }

        // If the identity isn't already set in cookie get the identity from the current user unless an identity is already pending push
        if (! $this->client->isIdentified() && $this->client->getPushCollection()->filter(fn($item) => $item[0] === 'identify')->isEmpty()) {
            return true;
        }

        return false;
    }
}
