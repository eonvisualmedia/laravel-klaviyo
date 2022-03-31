<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoTrack;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use EonVisualMedia\LaravelKlaviyo\TrackEvent;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

class TrackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Klaviyo::partialMock()->shouldReceive('getIdentity')->andReturn(['$exchange_id' => 'foo']);
    }

    public function test_track_job_dispatched()
    {
        Http::fake();

        $this->travelTo(now());

        Klaviyo::track(TrackEvent::make('foo'));

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/track' &&
                Arr::get($request, 'event') === 'foo' &&
                Arr::get($request, 'customer_properties') === ['$exchange_id' => 'foo'] &&
                Arr::get($request, 'time') === now()->getTimestamp() &&
                ! Arr::has($request, 'properties');
        });
    }

    public function test_track_job_request()
    {
        Http::fake();

        $this->travelTo(now());

        Klaviyo::track(TrackEvent::make('foo', ['foo' => 'bar']));

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/track' &&
                $request['event'] === 'foo' &&
                $request['customer_properties'] === ['$exchange_id' => 'foo'] &&
                $request['time'] === now()->getTimestamp() &&
                $request['properties'] === ['foo' => 'bar'];
        });
    }

    public function test_track_does_not_dispatch_when_disabled()
    {
        Bus::fake();

        Klaviyo::disable();

        Klaviyo::track(TrackEvent::make('foo'));

        Bus::assertNotDispatched(SendKlaviyoTrack::class);
    }

    public function test_track_multiple_requests()
    {
        Http::fake();

        $this->travelTo(now());

        Klaviyo::track(
            TrackEvent::make('foo'),
            TrackEvent::make('foo', ['foo' => 'bar'])
        );

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/track' &&
                Arr::get($request, 'event') === 'foo' &&
                Arr::get($request, 'customer_properties') === ['$exchange_id' => 'foo'] &&
                Arr::get($request, 'time') === now()->getTimestamp() &&
                ! Arr::has($request, 'properties');
        });

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/track' &&
                Arr::get($request, 'event') === 'foo' &&
                Arr::get($request, 'customer_properties') === ['$exchange_id' => 'foo'] &&
                Arr::get($request, 'time') === now()->getTimestamp() &&
                Arr::get($request, 'properties') === ['foo' => 'bar'];
        });
    }
}
