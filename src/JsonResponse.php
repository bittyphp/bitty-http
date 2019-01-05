<?php

namespace Bitty\Http;

use Bitty\Http\Response;

class JsonResponse extends Response
{
    /**
     * @param mixed $body Any value that can be JSON encoded.
     * @param int $statusCode
     * @param array $headers Array of string|string[]
     */
    public function __construct(
        $body = '',
        $statusCode = 200,
        array $headers = []
    ) {
        $json = json_encode($body);
        if (false === $json) {
            throw new \RuntimeException('Failed to encode data as JSON.');
        }

        parent::__construct($json, $statusCode, $headers);

        // forcibly override content type
        $this->headers = $this->withHeader('Content-Type', 'application/json')->getHeaders();
    }
}
