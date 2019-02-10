<?php

namespace Bitty\Http;

use Bitty\Http\AbstractMessage;
use Bitty\Http\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends AbstractMessage implements RequestInterface
{
    /**
     * HTTP method being used, e.g. GET, POST, etc.
     *
     * @var string
     */
    protected $method = null;

    /**
     * Valid HTTP methods.
     *
     * Verified 2019-01-20
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
     *
     * @var string[]
     */
    protected $validMethods = [
        'OPTIONS',
        'HEAD',
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'TRACE',
        'CONNECT',
    ];

    /**
     * URI of the request.
     *
     * @var UriInterface
     */
    protected $uri = null;

    /**
     * HTTP request target.
     *
     * @var string
     */
    protected $requestTarget = null;

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array $headers
     * @param StreamInterface|resource|string $body
     * @param string $protocolVersion
     */
    public function __construct(
        string $method = 'GET',
        $uri = '',
        array $headers = [],
        $body = '',
        string $protocolVersion = '1.1'
    ) {
        $this->method  = $this->filterMethod($method);
        $this->uri     = new Uri(strval($uri));
        $this->headers = $this->filterHeaders($headers);
        $this->body    = $this->filterBody($body);
        $this->protocolVersion = $this->filterProtocolVersion($protocolVersion);
    }

    public function __clone()
    {
        $this->uri  = clone $this->uri;
        $this->body = clone $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget === null) {
            $string = '/'.ltrim($this->uri->getPath(), '/');

            $query = $this->uri->getQuery();
            if ($query !== '') {
                $string .= '?'.$query;
            }

            return $string;
        }

        return $this->requestTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        $request = clone $this;

        $request->requestTarget = $this->filterRequestTarget($requestTarget);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method): RequestInterface
    {
        $request = clone $this;

        $request->method = $this->filterMethod($method);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): UriInterface
    {
        return clone $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        $request = clone $this;

        $request->uri = clone $uri;

        $newHost = $uri->getHost();
        if ($preserveHost) {
            if ($this->getHeaderLine('Host') === '' && $newHost !== '') {
                return $request->withHeader('Host', $newHost);
            }
        } elseif ($newHost !== '') {
            return $request->withHeader('Host', $newHost);
        }

        return $request;
    }

    /**
     * Filters HTTP method to make sure it's valid.
     *
     * The given string should not be modified.
     *
     * @param string $method
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function filterMethod(string $method): string
    {
        if (!in_array(strtoupper($method), $this->validMethods, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'HTTP method "%s" is invalid. Valid methods are: ["%s"]',
                    $method,
                    implode('", "', $this->validMethods)
                )
            );
        }

        return $method;
    }

    /**
     * Filters request target to make sure it's valid.
     *
     * @param string $requestTarget
     *
     * @return string
     */
    private function filterRequestTarget(string $requestTarget): string
    {
        return $requestTarget;
    }
}
