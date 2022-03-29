<?php

namespace EonVisualMedia\LaravelKlaviyo\Test;

use EonVisualMedia\LaravelKlaviyo\Klaviyo;

class EnabledTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Klaviyo::partialMock()->shouldReceive('getIdentity')->andReturn(['$exchange_id' => 'foo']);
    }

    public function test_is_enabled_is_true()
    {
        $this->assertTrue(Klaviyo::isEnabled());
    }

    public function test_set_disabled()
    {
        Klaviyo::disable();

        $this->assertFalse(Klaviyo::isEnabled());
    }

    public function test_set_enabled()
    {
        Klaviyo::disable();

        Klaviyo::enable();

        $this->assertTrue(Klaviyo::isEnabled());
    }

    public function test_config_disabled()
    {
        $this->refreshApplication();

        config(['klaviyo.enabled' => false]);

        $this->assertFalse(Klaviyo::isEnabled());
    }
}
