<?php

namespace Bitty\Http;

use Bitty\Http\AbstractMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends AbstractMessage implements ResponseInterface
{
    /**
     * Valid HTTP status codes and reasons.
     *
     * Verified 2019-01-20
     *
     * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @var string[]
     */
    protected $validStatusCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $statusCode = null;

    /**
     * HTTP reason phrase.
     *
     * @var string
     */
    protected $reasonPhrase = null;

    /**
     * @param StreamInterface|resource|string $body
     * @param int $statusCode
     * @param array $headers Array of string|string[]
     */
    public function __construct(
        $body = '',
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->body         = $this->filterBody($body);
        $this->headers      = $this->filterHeaders($headers);
        $this->statusCode   = $this->filterStatusCode($statusCode);
        $this->reasonPhrase = $this->filterReasonPhrase('');
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * {@inheritDoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $message = clone $this;

        $message->statusCode   = $this->filterStatusCode($code);
        $message->reasonPhrase = $this->filterReasonPhrase(
            $reasonPhrase,
            $message->statusCode
        );

        return $message;
    }

    /**
     * Filters a status code to make sure it's valid.
     *
     * @param int $statusCode
     *
     * @return int
     *
     * @throws \InvalidArgumentException When invalid code given.
     */
    protected function filterStatusCode(int $statusCode): int
    {
        if (!isset($this->validStatusCodes[$statusCode])) {
            throw new \InvalidArgumentException(
                sprintf('HTTP status code "%s" is invalid.', $statusCode)
            );
        }

        return $statusCode;
    }

    /**
     * Filters a reason phrase to make sure it's valid.
     *
     * @param string $reasonPhrase
     * @param int|null $statusCode
     *
     * @return string
     */
    protected function filterReasonPhrase(string $reasonPhrase, int $statusCode = null): string
    {
        if (null === $statusCode) {
            $statusCode = $this->statusCode;
        }

        if (empty($reasonPhrase)
            && !empty($this->validStatusCodes[$statusCode])
        ) {
            return $this->validStatusCodes[$statusCode];
        }

        return $reasonPhrase;
    }
}
