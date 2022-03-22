<?php

namespace EonVisualMedia\LaravelKlaviyo\View\Composers;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IdentityComposer
{
    public function __construct(protected KlaviyoClient $client, protected array $identity = [])
    {
    }

    public function compose(View $view)
    {
        if (! empty($this->identity)) {
            // If identity array is provided always include it
            $view->with('identity', $this->identity);
        } elseif (! $this->client->isIdentified()) {
            // If the identity isn't already set in cookie get the identity from the current user
            $user = Auth::user();
            if ($user instanceof KlaviyoIdentity) {
                $view->with('identity', $user->getKlaviyoIdentity());
            }
        }
    }
}
