<?php

namespace EonVisualMedia\LaravelKlaviyo\View\Creators;

use EonVisualMedia\LaravelKlaviyo\KlaviyoClient;
use Illuminate\Support\Js;
use Illuminate\View\View;

class InitializeCreator
{
    public function __construct(protected KlaviyoClient $client)
    {
    }

    public function create(View $view)
    {
        $view->with('data', $this->client->getPushCollection()
            ->map(fn ($value) => array_map(fn ($item) => Js::from($item), $value))
        );
    }
}
