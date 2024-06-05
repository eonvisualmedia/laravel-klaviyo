<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoTrack;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use EonVisualMedia\LaravelKlaviyo\TrackEvent;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class TrackTest extends TestCase
{
    protected function setUpMock($identity = ['_kx' => 'foo'])
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
                $request->url() === 'https://a.klaviyo.com/api/events' &&
                Arr::get($request, 'data.attributes.metric.data.attributes.name') === 'foo' &&
                Arr::get($request, 'data.attributes.profile.data.attributes._kx') === 'foo' &&
                Arr::get($request, 'data.attributes.time') === now()->toIso8601String();
        });
    }

    public function test_track_job_request()
    {
        $this->setUpMock();

        Http::fake();

        $this->travelTo(now());

        Klaviyo::track(TrackEvent::make('foo', ['properties' => ['foo' => 'bar']]));

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/events' &&
                Arr::get($request, 'data.attributes.metric.data.attributes.name') === 'foo' &&
                Arr::get($request, 'data.attributes.profile.data.attributes._kx') === 'foo' &&
                Arr::get($request, 'data.attributes.time') === now()->toIso8601String() &&
                Arr::get($request, 'data.attributes.properties') === ['foo' => 'bar'];
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
            TrackEvent::make('foo', ['properties' => ['foo' => 'bar']]),
        );

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/events' &&
                Arr::get($request, 'data.attributes.metric.data.attributes.name') === 'foo' &&
                Arr::get($request, 'data.attributes.profile.data.attributes._kx') === 'foo' &&
                Arr::get($request, 'data.attributes.time') === now()->toIso8601String();
        });

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/events' &&
                Arr::get($request, 'data.attributes.metric.data.attributes.name') === 'foo' &&
                Arr::get($request, 'data.attributes.profile.data.attributes._kx') === 'foo' &&
                Arr::get($request, 'data.attributes.time') === now()->toIso8601String() &&
                Arr::get($request, 'data.attributes.properties') === ['foo' => 'bar'];
        });
    }

    public function test_track_request_invalid_throws()
    {
        $this->setUpMock();

        Http::fake([
            '*' => Http::response([
                'errors' => [
                    [
                        "id"     => "string",
                        "code"   => 401,
                        "title"  => "string",
                        "detail" => "string",
                        "source" => [
                            "pointer"   => "string",
                            "parameter" => "string"
                        ]
                    ]
                ]
            ], 401)
        ]);

        $this->expectException(RequestException::class);

        Klaviyo::track(TrackEvent::make('foo'));
    }

    public function test_track_request_server_error_throws()
    {
        $this->setUpMock();

        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $this->expectException(RequestException::class);

        Klaviyo::track(TrackEvent::make('foo'));
    }

    public function test_track_does_not_dispatch_when_no_identity()
    {
        $this->setUpMock(null);

        Http::fake();

        Bus::fake();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Identify requires one of the following fields: email, id, phone_number, _kx');

        Klaviyo::track(TrackEvent::make('foo'));

        Bus::assertNotDispatched(SendKlaviyoTrack::class);
    }
}
