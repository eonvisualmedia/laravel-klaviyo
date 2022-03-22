<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\View\Composers\IdentityComposer;
use EonVisualMedia\LaravelKlaviyo\View\Creators\InitializeCreator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LaravelKlaviyoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/klaviyo.php' => config_path('klaviyo.php'),
        ]);

        View::composer('klaviyo::identify', IdentityComposer::class);

        View::creator('klaviyo::initialize', InitializeCreator::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/klaviyo.php', 'klaviyo'
        );

        $this->loadViewsFrom(
            __DIR__.'/../resources/views', 'klaviyo'
        );

        // Don't register if not configured.
        if ($this->stop()) {
            return;
        }

        $this->app->singleton(KlaviyoClient::class, function () {
            $client = new KlaviyoClient(
                config('klaviyo.private_api_key'),
                config('klaviyo.public_api_key')
            );

            if (! is_null($identityKeyName = config('klaviyo.identity_key_name'))) {
                $client->setIdentityKeyName($identityKeyName);
            }

            return $client;
        });

        $this->app->alias(KlaviyoClient::class, 'klaviyo');
    }

    private function stop(): bool
    {
        return empty(config('klaviyo.private_api_key')) || empty(config('klaviyo.public_api_key'));
    }
}
