<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;

class EnabledTest extends TestCase
{
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
        config(['klaviyo.enabled' => false]);

        $this->assertFalse(Klaviyo::isEnabled());
    }
}
