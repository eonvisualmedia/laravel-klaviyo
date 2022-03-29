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
            ->assertSee('https://static.klaviyo.com/onsite/js/klaviyo.js?company_id=bar')
            ->assertSeeText("_learnq.push(['identify', JSON.parse('{\u0022\$email\u0022:\u0022foo@bar\u0022}')]);", false)
            ->assertSeeText("_learnq.push(['track', 'event', JSON.parse('{\u0022foo\u0022:\u0022bar\u0022}')]);", false)
            ->assertSeeText("_learnq.push(['track', 'event']);", false);
    }
}
