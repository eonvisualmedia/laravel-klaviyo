<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use EonVisualMedia\LaravelKlaviyo\Exceptions\KlaviyoException;
use EonVisualMedia\LaravelKlaviyo\Jobs\SendKlaviyoIdentify;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;

class IdentifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }

    public function test_identify_string()
    {
        Klaviyo::identify('foo');

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/identify' &&
                $request['properties'] === ['$email' => 'foo'];
        });
    }

    public function test_identify_klaviyo_identity()
    {
        $identity = $this->mock(KlaviyoIdentity::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('getKlaviyoIdentity')
                ->andReturn([
                    '$email'      => 'foo',
                    '$first_name' => 'Foo',
                    '$last_name'  => 'Bar',
                ]);
        });

        Klaviyo::identify($identity);

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://a.klaviyo.com/api/identify' &&
                $request['properties'] === [
                    '$email'      => 'foo',
                    '$first_name' => 'Foo',
                    '$last_name'  => 'Bar',
                ];
        });
    }

    public function test_identify_does_not_dispatch_when_null()
    {
        Bus::fake();

        $this->expectException(KlaviyoException::class);

        Klaviyo::identify();

        Bus::assertNotDispatched(SendKlaviyoIdentify::class);
    }

    public function test_identify_does_not_dispatch_when_invalid()
    {
        Bus::fake();

        $this->expectException(KlaviyoException::class);

        Klaviyo::identify(['foo' => 'bar']);

        Bus::assertNotDispatched(SendKlaviyoIdentify::class);
    }

    public function test_identify_does_not_dispatch_when_disabled()
    {
        Bus::fake();

        Klaviyo::disable();

        Klaviyo::identify('foo');

        Bus::assertNotDispatched(SendKlaviyoIdentify::class);
    }
}
