<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Feature;

use EonVisualMedia\LaravelKlaviyo\Test\TestCase;

class IdentityTest extends TestCase
{
    public function test_can_get_identity_with_exchange_id()
    {
        $this
            ->withKlaviyoCookie([
                '$exchange_id' => 'foo',
            ])
            ->get('identity')
            ->assertExactJson([
                '$exchange_id' => 'foo',
            ]);
    }

    public function test_can_get_identity_with_email()
    {
        $this
            ->withKlaviyoCookie([
                '$email' => 'foo@bar',
            ])
            ->get('identity')
            ->assertExactJson([
                '$email' => 'foo@bar',
            ]);
    }

    public function test_get_identity_without_cookie_is_null()
    {
        $this
            ->get('identity')
            ->assertNoContent();
    }

    public function test_get_identity_empty_cookie_is_null()
    {
        $this
            ->withKlaviyoCookie([
                // Empty
            ])
            ->get('identity')
            ->assertNoContent();
    }
}
