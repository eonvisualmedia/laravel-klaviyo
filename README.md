# laravel-klaviyo

This package assists with interacting with [Klaviyo](https://www.klaviyo.com/) to track client and server-side metrics and the REST api.

## Requirements

For server-side track, identify or REST api calls this package utilises the Laravel HTTP Client.

It is recommended that server-side events are processed in the background, by default jobs are placed on the `klaviyo` queue.

## Installation

You can install the package via composer:

```bash
composer require eonvisualmedia/laravel-klaviyo
```

The package will automatically register itself.

You can optionally publish the config file with:

```bash
php artisan vendor:publish --provider="EonVisualMedia\LaravelKlaviyo\LaravelKlaviyoServiceProvider" --tag="tags-config"
```

Depending upon your intended usage minimally you'll need to configure your environment with your public and private api keys.

```
// .env

KLAVIYO_PRIVATE_API_KEY=
KLAVIYO_PUBLIC_API_KEY=
```

## Usage

### Basic Example

First you'll need to include the Klaviyo JavaScript API for Identification and Tracking by including it at the end of your layout just before the closing body tag.

```
// layout.blade.php

<html>
  <body>
    {{-- ... --}}
    @include('klaviyo::initialize')
  </body>
</html>
```

#### To add identity

If the current user is not identified and `Auth::user()` is an instance of `EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity` then the `getKlaviyoIdentity` method will be called and an identify
event added to the page.

Alternatively the identify method may be called explicitly, for instance after user login.

```php
Klaviyo::identify([
    'email' => 'foo@example.com',
    'first_name' => 'Foo',
    'last_name' => 'Bar'
]);
```

#### Track events client-side

```php
Klaviyo::push('track', 'Added to Cart', [
    '$value' => 100,
    'AddedTitle' => 'Widget A'
]);
```

#### Track events server-side:

To queue server-side events.

```php
Klaviyo::track(TrackEvent::make(
    'Placed Order',
    [
        'unique_id' => '1234_WINNIEPOOH',
        'value' => 9.99,
    ]
));
```

You can optionally also specify the customer properties and timestamp, if not specified the customer will attempt to be identified by their cookie ($exchange_id) or their user model if `Auth::user()`
is an instance of `EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity`.

```php
Klaviyo::track(TrackEvent::make(
    'Placed Order',
    [
         'unique_id' => '1234_WINNIEPOOH',
         'value' => 9.99,
    ],
    [
        'email' => 'foo@example.com',
        'first_name' => 'Foo',
        'last_name' => 'Bar',
    ],
    now()->addWeeks(-1)
));
```

### Monitoring Login Events

By default, the package will subscribe to `Illuminate\Auth\Events\Login` events and dispatch an `klaviyo.identify(...)` call.

This behaviour can be disabled using the config option `identify_on_login`.

### Advanced usage

#### Macros

The package allows you to extend its functionality, this can be helpful for creating reusable events.

You may define macros within the `boot` method of a service provider, either your own or within the application's `App\Providers\AppServiceProvider` class.

```php
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
 
/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Klaviyo::macro(
        'fulfilled_order',
        function (Transaction $transaction) {
            Klaviyo::track(TrackEvent::make(
                'Fulfilled Order',
                $transaction->toKlaviyo(),
                $transaction->user,
                $transaction->created_at
            ));
        }
    );
}
```

With the macro defined you may invoke it anywhere in your application:

```php
Klaviyo::fulfilled_order($transaction);
```

#### REST

You may interact with the Klaviyo REST api using the Laravel HTTP Client, calls forwarded via KlaviyoClient append a `Authorization: Klaviyo-API-Key your-private-api-key` header to requests.

```php
Klaviyo::get('lists');

Klaviyo::post("profile-subscription-bulk-create-jobs", [
    'data' => [
        'type'          => 'profile-subscription-bulk-create-job',
        'attributes'    => [
            'profiles' => [
                'data' => [
                    [
                        'type'       => 'profile',
                        'attributes' => [
                            'email'         => 'foo@example.com',
                            'subscriptions' => [
                                'email' => [
                                    'marketing' => [
                                        'consent' => 'SUBSCRIBED'
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'relationships' => [
            'list' => [
                'data' => [
                    'type' => 'list',
                    'id'   => $list_id
                ]
            ]
        ]
    ]
]);

Klaviyo::delete("profile-subscription-bulk-delete-jobs", [
    'data' => [
        'type'          => 'profile-subscription-bulk-delete-job',
        'attributes'    => [
            'profiles' => [
                'data' => [
                    [
                        'type'       => 'profile',
                        'attributes' => [
                            'email' => 'foo@example.com',
                        ]
                    ]
                ]
            ]
        ],
        'relationships' => [
            'list' => [
                'data' => [
                    'type' => 'list',
                    'id'   => $list_id
                ]
            ]
        ]
    ]
]);
```

## Upgrading from v1

The [Klaviyo](https://www.klaviyo.com/) legacy v1/v2 APIs are scheduled to retire June 30th, 2024.

I would encourage you to review especially the breaking changes on the [Klaviyo: API versioning and deprecation policy](https://developers.klaviyo.com/en/docs/api_versioning_and_deprecation_policy)

The API changes have therefore necessitated a few breaking changes to this package, specifically the payloads required for `identify` and `push`.
See the examples below, for example the `getKlaviyoIdentity` response replaces `$email` with `email`.

Also take note that client and server payloads for identify/profile have a few differences `klaviyo_client_to_server_profile` may be of assistance help converting client payloads to server profiles.
