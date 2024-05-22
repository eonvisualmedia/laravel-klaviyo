<?php

namespace EonVisualMedia\LaravelKlaviyo;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use InvalidArgumentException;

class TrackEvent
{
    public array $identity;
    public Carbon $time;

    public function __construct(
        public string                $metric_name,
        public array                 $properties = [],
        KlaviyoIdentity|string|array $identity = null,
        Carbon                       $time = null
    )
    {
        $this->identity = Klaviyo::resolveIdentity($identity);
        $this->time = $time ?? Carbon::now();
    }

    /**
     * @deprecated 2.0.0
     * @see __construct
     */
    public static function make(
        string                       $metric_name,
        array                        $properties = [],
        KlaviyoIdentity|string|array $identity = null,
        Carbon                       $timestamp = null
    ): TrackEvent
    {
        return new static($metric_name, $properties, $identity, $timestamp);
    }
}
