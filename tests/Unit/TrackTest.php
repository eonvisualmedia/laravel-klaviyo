<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Exceptions\KlaviyoException;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoTrack;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use EonVisualMedia\LaravelKlaviyo\TrackEvent;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

class TrackTest extends TestCase
{
    protected function setUpMock($identity = ['$exchange_id' => 'foo'])
    {
        Klaviyo::partialMock()->shouldReceive('getIdentity')->andReturn($identity);
    }

    public function test_track_job_dispatched()
    {
        $this->setUpMock();

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
        $this->setUpMock();

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
        $this->setUpMock();

        Bus::fake();

        Klaviyo::disable();

        Klaviyo::track(TrackEvent::make('foo'));

        Bus::assertNotDispatched(SendKlaviyoTrack::class);
    }

    public function test_track_multiple_requests()
    {
        $this->setUpMock();

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

    public function test_track_request_invalid_throws()
    {
        $this->setUpMock();

        Http::fake([
            'track' => Http::response('0', 200),
        ]);

        $this->expectException(KlaviyoException::class);

        Klaviyo::track(TrackEvent::make('foo'));
    }

    public function test_track_request_server_error_throws()
    {
        $this->setUpMock();

        Http::fake([
            'track' => Http::response(null, 500),
        ]);

        $this->expectException(RequestException::class);

        Klaviyo::track(TrackEvent::make('foo'));
    }

    public function test_track_does_not_dispatch_when_no_identity()
    {
        $this->setUpMock(null);

        Http::fake();

        Bus::fake();

        $this->expectException(KlaviyoException::class);
        $this->expectExceptionMessage('Identify requires one of the following fields: $email, $id, $phone_number, $exchange_id');

        Klaviyo::track(TrackEvent::make('foo'));

        Bus::assertNotDispatched(SendKlaviyoTrack::class);
    }
}
