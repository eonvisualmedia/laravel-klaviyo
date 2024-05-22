<?php

namespace EonVisualMedia\LaravelKlaviyo\Test;

use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\LaravelKlaviyoServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('klaviyo.private_api_key', 'private');
            $config->set('klaviyo.public_api_key', 'public');
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelKlaviyoServiceProvider::class,
        ];
    }

    protected function defineWebRoutes($router)
    {
        $router->get('/identity', function () {
            return with(Klaviyo::getIdentity(), function ($identity) {
                if ($identity === null) {
                    return response()->noContent();
                } else {
                    return response()->json($identity);
                }
            });
        });

        $router->view('/render', 'klaviyo::initialize');
    }

    protected function withKlaviyoCookie(array $value): TestCase
    {
        return $this
            ->withUnencryptedCookie('__kla_id', base64_encode(json_encode($value)));
    }
}
