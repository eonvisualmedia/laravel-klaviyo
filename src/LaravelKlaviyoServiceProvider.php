<?php

namespace EonVisualMedia\LaravelKlaviyo;

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
            __DIR__ . '/../config/klaviyo.php' => config_path('klaviyo.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/klaviyo.php', 'klaviyo'
        );

        // Don't register if not configured.
        if ($this->stop()) {
            return;
        }

        $this->app->singleton(KlaviyoClient::class, function () {
            return new KlaviyoClient(config('klaviyo.private_api_key'), config('klaviyo.public_api_key'));
        });

        $this->app->alias(KlaviyoClient::class, 'klaviyo');
    }

    private function stop(): bool
    {
        return empty(config('klaviyo.private_api_key')) || empty(config('klaviyo.public_api_key'));
    }
}
