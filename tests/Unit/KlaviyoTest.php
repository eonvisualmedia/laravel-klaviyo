<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;

class KlaviyoTest extends TestCase
{
    public function test_config_endpoint()
    {
        config([
            'klaviyo.endpoint' => 'foo',
        ]);

        $this->assertEquals('foo', Klaviyo::getEndpoint());
    }

    public function test_get_keys()
    {
        config([
            'klaviyo.private_api_key' => 'foo',
            'klaviyo.public_api_key' => 'bar',
        ]);

        $this->assertEquals('foo', Klaviyo::getPrivateKey());
        $this->assertEquals('bar', Klaviyo::getPublicKey());
    }

    public function test_config_identity_key_name()
    {
        config([
            'klaviyo.identity_key_name' => '$id',
        ]);

        $this->assertEquals('$id', Klaviyo::getIdentityKeyName());
    }
}
