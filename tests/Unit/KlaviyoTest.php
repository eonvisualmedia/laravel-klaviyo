<?php

namespace EonVisualMedia\LaravelKlaviyo\Test;

use EonVisualMedia\LaravelKlaviyo\Klaviyo;

class KlaviyoTest extends TestCase
{
    public function test_set_base_uri()
    {
        Klaviyo::setBaseUri('foo');

        $this->assertEquals('foo', Klaviyo::getBaseUri());
    }

    public function test_get_keys()
    {
        config([
            'klaviyo.private_api_key' => 'foo',
            'klaviyo.public_api_key'  => 'bar',
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
