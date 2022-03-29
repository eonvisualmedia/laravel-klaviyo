<?php

namespace EonVisualMedia\LaravelKlaviyo\View\Creators;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;
use Illuminate\View\View;

class InitializeCreator
{
    public function __construct(protected KlaviyoClient $client)
    {
    }

    public function create(View $view)
    {
        // If the identity isn't already set in cookie get the identity from the current user unless an identity is already pending push
        if (! $this->client->isIdentified() && $this->client->getPushCollection()->filter(fn ($item) => $item[0] === 'identify')->isEmpty()) {
            $user = Auth::user();
            if ($user instanceof KlaviyoIdentity) {
                $this->client->prepend('identify', $user->getKlaviyoIdentity());
            }
        }

        $view
            ->with('enabled', $this->client->isEnabled())
            ->with('publicKey', $this->client->getPublicKey())
            ->with('data', $this->client->getPushCollection()
                ->map(fn ($value) => array_map(fn ($item) => Js::from($item), $value))
            );
    }
}
