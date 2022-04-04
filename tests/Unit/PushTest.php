<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Contracts\ViewedProduct;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use InvalidArgumentException;
use Mockery\MockInterface;

class PushTest extends TestCase
{
    public function test_prepend_zero_throws()
    {
        $this->expectException(InvalidArgumentException::class);

        Klaviyo::prepend();
    }

    public function test_prepend_invalid_throws()
    {
        $this->expectException(InvalidArgumentException::class);

        Klaviyo::prepend('track', 'event', ['foo' => 'bar'], 'too many arguments');
    }

    public function test_push_zero_throws()
    {
        $this->expectException(InvalidArgumentException::class);

        Klaviyo::push();
    }

    public function test_push_one()
    {
        Klaviyo::push('track');

        $this->assertTrue(Klaviyo::getPushCollection()->contains(['track']));
    }

    public function test_push_two()
    {
        Klaviyo::push('track', 'event');

        $this->assertTrue(Klaviyo::getPushCollection()->contains(['track', 'event']));
    }

    public function test_push_three()
    {
        Klaviyo::push('track', 'event', ['foo' => 'bar']);

        $this->assertTrue(Klaviyo::getPushCollection()->contains(['track', 'event', ['foo' => 'bar']]));
    }

    public function test_push_invalid_throws()
    {
        $this->expectException(InvalidArgumentException::class);

        Klaviyo::push('track', 'event', ['foo' => 'bar'], 'too many arguments');
    }

    public function test_push_viewed_product()
    {
        $product = $this->mock(ViewedProduct::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('getViewedProductProperties')
                ->andReturn([
                    'foo' => 'bar',
                ]);
        });

        Klaviyo::pushViewed($product);

        $this->assertTrue(Klaviyo::getPushCollection()->contains(['track', 'Viewed Product', ['foo' => 'bar']]));
        $this->assertTrue(Klaviyo::getPushCollection()->contains(['trackViewedItem', ['foo' => 'bar']]));
    }
}
