<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Feature;

use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use EonVisualMedia\LaravelKlaviyo\Test\TestUser;

class RenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'klaviyo.public_api_key' => 'bar',
        ]);
    }

    public function test_render_identity_and_pushes()
    {
        Klaviyo::push('track', 'event', ['foo' => 'bar']);
        Klaviyo::push('track', 'event');

        $user = new TestUser(['email' => 'foo@bar']);

        $this
            ->actingAs($user)
            ->get('/render')
            ->assertSee([
                'klaviyo.identify({"email":"foo@bar"}, function () {',
                'klaviyo.push(["track", "event", {"foo":"bar"}]);',
                'klaviyo.push(["track", "event"]);'
            ], false);
    }
}
