<?php

namespace Bitty\Http;

use Bitty\Http\Response;

class RedirectResponse extends Response
{
    /**
     * Creates a response that redirects to a new URI.
     *
     * @param string $uri URI to redirect to.
     * @param int $statusCode HTTP status code.
     * @param array $headers Array of string|string[]
     */
    public function __construct($uri, $statusCode = 302, array $headers = [])
    {
        $body = '<html><body><p>This page has been moved <a href="'
            .htmlentities($uri).'">here</a>.</p></body></html>';

        parent::__construct($body, $statusCode, $headers);

        // forcibly override location
        $this->headers = $this->withHeader('Location', $uri)->getHeaders();
    }
}
