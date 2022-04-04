<?php

namespace EonVisualMedia\LaravelKlaviyo;

use Illuminate\Support\Facades\Facade;

class Klaviyo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'klaviyo';
    }
}
