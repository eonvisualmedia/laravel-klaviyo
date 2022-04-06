# laravel-klaviyo

This package assists with interacting with Klaviyo to track client and server-side metrics and the REST api.

## Requirements

For server-side track, identify or REST api calls this package utilises the Laravel HTTP Client, therefore if required you should install `guzzlehttp/guzzle` via Composer:

```bash
composer require guzzlehttp/guzzle
```

Additionally, server-side events are queued you should configure a queue worker to process jobs which by default are on the `klaviyo` queue.

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
    '$email' => 'foo@example.com',
    '$first_name' => 'Foo',
    '$last_name' => 'Bar'
]);
```

#### Track events client-side

```php
Klaviyo::push('track', 'Added to Cart' [
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
        '$event_id' => '1234_WINNIEPOOH',
        '$value' => 9.99,
    ]
));
```

You can optionally also specify the customer properties and timestamp, if not specified the customer will attempt to be identified by their cookie ($exchange_id) or their user model if `Auth::user()`
is an instance of `EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity`.

```php
Klaviyo::track(TrackEvent::make(
    'Placed Order',
    [
         '$event_id' => '1234_WINNIEPOOH',
         '$value' => 9.99,
    ],
    [
        '$email' => 'foo@example.com',
        '$first_name' => 'Foo',
        '$last_name' => 'Bar'
    ],
    now()->addWeeks(-1)
));
```

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

You may interact with the Klaviyo REST api using the Laravel HTTP Client, helper methods exposed on this package append the `api_key=PRIVATE_API_KEY` to requests.

```php
Klaviyo::delete('v2/list/{list_id}/subscribe', [
    'emails' => ['foo@example.com']
]);

Klaviyo::get('v2/lists');

Klaviyo::post('v2/list/{list_id}/subscribe', [
    'profiles' => [
        ['email' => 'foo@example.com']
    ]
]);
```
