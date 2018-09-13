<?php

namespace Bitty\Http;

use Bitty\Http\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RequestFactory implements RequestFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
