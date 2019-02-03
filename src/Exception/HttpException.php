<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpException extends \Exception implements HttpExceptionInterface
{
    /**
     * The request object, if available.
     *
     * @var ServerRequestInterface|null
     */
    protected $request = null;

    /**
     * The response object, if available.
     *
     * @var ResponseInterface|null
     */
    protected $response = null;

    /**
     * Title of the exception, e.g. "404 Not Found"
     *
     * @var string
     */
    protected $title = '';

    /**
     * Description of the exception.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Default message to use.
     *
     * @var string
     */
    protected $message = '';

    /**
     * @param string|null $message
     * @param int $code
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @param \Exception|null $previous
     */
    public function __construct(
        $message = null,
        $code = 0,
        ServerRequestInterface $request = null,
        ResponseInterface $response = null,
        \Exception $previous = null
    ) {
        if (null === $message) {
            $message = $this->message;
        }

        if (empty($code)) {
            $code = $this->code;
        }

        parent::__construct($message, $code, $previous);

        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
