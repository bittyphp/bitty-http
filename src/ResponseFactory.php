<?php

namespace Bitty\Http;

use Bitty\Http\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new Response();

        return $response->withStatus($code, $reasonPhrase);
    }
}
