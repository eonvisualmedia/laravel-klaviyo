<?php

namespace EonVisualMedia\LaravelKlaviyo\Test\Unit;

use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use EonVisualMedia\LaravelKlaviyo\Test\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class RestTest extends TestCase
{
    public function test_rest_calls()
    {
        Http::fake();

        Klaviyo::get('get');
        Klaviyo::head('head');
        Klaviyo::post('post');
        Klaviyo::patch('patch');
        Klaviyo::delete('delete');

        Http::assertSent(fn (Request $request) => $request->method() === 'GET');
        Http::assertSent(fn (Request $request) => $request->method() === 'HEAD');
        Http::assertSent(fn (Request $request) => $request->method() === 'POST');
        Http::assertSent(fn (Request $request) => $request->method() === 'PATCH');
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE');
    }
}
