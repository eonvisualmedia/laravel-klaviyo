<?php

namespace EonVisualMedia\LaravelKlaviyo\Http\Middleware;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TrackAndIdentify
{
    /**
     * Map unprocessable requests to HTTP 422 responses.
     *
     * @return \Closure
     */
    public static function middleware(): \Closure
    {
        return static function (callable $handler): callable {
            return static function (RequestInterface $request, array $options) use ($handler) {
                return $handler($request, $options)
                    ->then(
                        static function (ResponseInterface $response) use ($request): ResponseInterface {
                            if ($response->getStatusCode() === 200 && (string) $response->getBody() === '0') {
                                throw RequestException::create($request, new Response(422, $response->getHeaders()));
                            }

                            return $response;
                        }
                    );
            };
        };
    }
}
