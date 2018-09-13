<?php

namespace Bitty\Http;

use Bitty\Http\ServerRequest;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, [], '', '1.1', [], [], [], [], $serverParams);
    }
}
