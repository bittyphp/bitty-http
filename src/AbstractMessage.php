<?php

namespace Bitty\Http;

use Bitty\Http\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * HTTP response body.
     *
     * @var StreamInterface
     */
    protected $body = null;

    /**
     * HTTP headers.
     *
     * @var array Array of string|string[]
     */
    protected $headers = [];

    /**
     * HTTP protocol version.
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * List of valid HTTP protocol versions.
     *
     * Updated 2017-12-23
     *
     * @var string[]
     */
    protected $validProtocolVersions = [
        '1.0',
        '1.1',
        '2.0',
        '2',
    ];

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion($version): MessageInterface
    {
        $message = clone $this;

        $message->protocolVersion = $this->filterProtocolVersion($version);

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader($name): bool
    {
        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($name): array
    {
        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                return $values;
            }
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader($name, $value): MessageInterface
    {
        $message = clone $this;
        $headers = [];

        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                $headers[$name] = $value;

                continue;
            }

            $headers[$header] = $values;
        }

        $headers[$name]   = $value;
        $message->headers = $this->filterHeaders($headers);

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        $message = clone $this;
        $headers = [];
        $found   = false;

        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                $found = true;
                foreach ((array) $value as $newValue) {
                    $values[] = $newValue;
                }
            }

            $headers[$header] = $values;
        }

        if (!$found) {
            $headers[$name] = $value;
        }

        $message->headers = $this->filterHeaders($headers);

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader($name): MessageInterface
    {
        $message = clone $this;
        $headers = [];

        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                continue;
            }

            $headers[$header] = $values;
        }

        $message->headers = $headers;

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): ?StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $message = clone $this;

        $message->body = $this->filterBody($body);

        return $message;
    }

    /**
     * Filters body content to make sure it's valid.
     *
     * @param StreamInterface|resource|string $body
     *
     * @return StreamInterface
     */
    protected function filterBody($body): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        return new Stream($body);
    }

    /**
     * Filters headers to make sure they're valid.
     *
     * @param string[] $headers
     *
     * @return array Array of string|string[]
     *
     * @throws \InvalidArgumentException
     */
    protected function filterHeaders(array $headers): array
    {
        foreach ($headers as $header => $values) {
            $this->validateHeader($header, $values);
            $headers[$header] = (array) $values;
        }

        return $headers;
    }

    /**
     * Filters protocol version to make sure it's valid.
     *
     * @param string $protocolVersion
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterProtocolVersion($protocolVersion): string
    {
        if (!in_array($protocolVersion, $this->validProtocolVersions, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid protocol version "%s". Valid versions are: ["%s"]',
                    $protocolVersion,
                    implode('", "', $this->validProtocolVersions)
                )
            );
        }

        return $protocolVersion;
    }

    /**
     * Validates a header name and values.
     *
     * @param string $header
     * @param string|string[] $values
     *
     * @throws \InvalidArgumentException
     */
    protected function validateHeader($header, $values = []): void
    {
        // TODO: validate $name
        // throw new \InvalidArgumentException()

        if (!is_string($values) && !is_array($values)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Values for header "%s" must be a string or array; %s given.',
                    $header,
                    gettype($values)
                )
            );
        }

        foreach ((array) $values as $value) {
            if (is_string($value)) {
                continue;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Values for header "%s" must contain only strings; %s given.',
                    $header,
                    gettype($value)
                )
            );
        }
    }
}
