<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\View\Creators\InitializeCreator;
use Illuminate\Cookie\Middleware\EncryptCookies;
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

        $this->app->singleton(KlaviyoClient::class, function () {
            return new KlaviyoClient(
                $this->app['config']->get('klaviyo', [])
            );
        });

        $this->app->alias(KlaviyoClient::class, 'klaviyo');

        $this->app->resolving(EncryptCookies::class, function (EncryptCookies $middleware) {
            $middleware->disableFor('__kla_id');
        });
    }
}
