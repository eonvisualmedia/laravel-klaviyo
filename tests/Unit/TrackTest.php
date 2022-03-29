<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoTrack;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use Illuminate\Http\Client\Request;
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
        Bus::fake();

        $this->freezeTime(function () {
            Klaviyo::track('foo');

            Bus::assertDispatched(SendKlaviyoTrack::class, function (SendKlaviyoTrack $job) {
                return $job->getEvent() === 'foo' &&
                    $job->getProperties() === null &&
                    $job->getIdentity() === ['$exchange_id' => 'foo'] &&
                    $job->getTimestamp() === now()->getTimestamp();
            });
        });
    }

    public function test_track_job_request()
    {
        Http::fake();

        $this->freezeTime(function () {
            Klaviyo::track('foo', ['foo' => 'bar']);

            Http::assertSent(function (Request $request) {
                return $request->method() === 'POST' &&
                    $request->url() === 'https://a.klaviyo.com/api/track' &&
                    $request['event'] === 'foo' &&
                    $request['customer_properties'] === ['$exchange_id' => 'foo'] &&
                    $request['time'] === now()->getTimestamp() &&
                    $request['properties'] === ['foo' => 'bar'];
            });
        });
    }

    public function test_track_does_not_dispatch_when_disabled()
    {
        Bus::fake();

        Klaviyo::disable();

        Klaviyo::track('foo');

        Bus::assertNotDispatched(SendKlaviyoTrack::class);
    }
}
