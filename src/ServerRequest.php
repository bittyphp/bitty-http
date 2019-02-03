<?php

namespace Bitty\Http;

use Bitty\Collection\ReadableArrayCollection;
use Bitty\Http\Headers;
use Bitty\Http\Request;
use Bitty\Http\RequestBody;
use Bitty\Http\UploadedFiles;
use Bitty\Http\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var ReadableArrayCollection
     */
    public $query = null;

    /**
     * @var ReadableArrayCollection
     */
    public $request = null;

    /**
     * Query parameters.
     *
     * @var string[]
     */
    protected $queryParams = null;

    /**
     * Cookie parameters.
     *
     * @var string[]
     */
    protected $cookies = null;

    /**
     * Uploaded files.
     *
     * @var UploadedFileInterface[]
     */
    protected $files = null;

    /**
     * Server parameters.
     *
     * @var string[]
     */
    protected $server = null;

    /**
     * Request attributes.
     *
     * @var mixed[]
     */
    protected $attributes = null;

    /**
     * Parsed request body.
     *
     * @var null|array|object
     */
    protected $parsedBody = null;

    /**
     * List of callables to parse different content types.
     *
     * @var callable[]
     */
    protected $contentTypeParsers = [];

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array $headers
     * @param StreamInterface|resource|string $body
     * @param string $protocolVersion
     * @param array $query
     * @param array $request
     * @param array $cookies
     * @param UploadedFileInterface[] $files
     * @param array $server
     * @param array $attributes
     */
    public function __construct(
        string $method = 'GET',
        $uri = '',
        array $headers = [],
        $body = '',
        string $protocolVersion = '1.1',
        array $query = [],
        array $request = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        array $attributes = []
    ) {
        parent::__construct($method, $uri, $headers, $body, $protocolVersion);

        $this->queryParams = $this->filterQueryParams($query);
        $this->cookies     = $this->filterCookieParams($cookies);
        $this->files       = $this->filterFileParams($files);
        $this->server      = $this->filterServerParams($server);
        $this->attributes  = $this->filterAttributes($attributes);

        if ('POST' === $this->method
            && in_array(
                $this->getContentType(),
                ['application/x-www-form-urlencoded', 'multipart/form-data'],
                true
            )
        ) {
            $this->parsedBody = $this->filterParsedBody($request);
        } else {
            $this->request = new ReadableArrayCollection([]);
        }

        $this->registerContentTypeParser('application/json', function ($body) {
            $json = json_decode($body, true);
            if (!is_array($json)) {
                return null;
            }

            return $json;
        });

        $this->registerContentTypeParser('application/x-www-form-urlencoded', function ($body) {
            $data = [];

            parse_str($body, $data);

            return $data;
        });
    }

    /**
     * Creates a new request from global variables.
     *
     * @return ServerRequestInterface
     */
    public static function createFromGlobals(): ServerRequestInterface
    {
        $server  = new Headers();
        $headers = $server->getHeaders($_SERVER);
        $method  = empty($_SERVER['REQUEST_METHOD']) ? 'GET' : $_SERVER['REQUEST_METHOD'];
        $uri     = Uri::createFromArray($_SERVER);
        $body    = new RequestBody();
        $files   = new UploadedFiles();

        $protocol = empty($_SERVER['SERVER_PROTOCOL'])
            ? '1.1'
            : $_SERVER['SERVER_PROTOCOL'];

        return new static(
            $method,
            $uri,
            $headers,
            $body,
            $protocol,
            $_GET,
            $_POST,
            $_COOKIE,
            $files->collapseFileTree($_FILES),
            $_SERVER,
            []
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $request = clone $this;

        $request->queryParams = $this->filterQueryParams($query);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $request = clone $this;

        $request->cookies = $this->filterCookieParams($cookies);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->files;
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $files): ServerRequestInterface
    {
        $request = clone $this;

        $request->files = $this->filterFileParams($files);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        if (null === $this->parsedBody) {
            $body = strval($this->body);

            $contentType = $this->getContentType();
            if (!isset($this->contentTypeParsers[$contentType])) {
                return $this->parsedBody;
            }

            $this->parsedBody = $this->filterParsedBody(
                $this->contentTypeParsers[$contentType]($body)
            );

            return $this->parsedBody;
        }

        return $this->parsedBody;
    }

    /**
     * @param null|array|object|mixed $parsedBody
     *
     * @return ServerRequestInterface
     */
    public function withParsedBody($parsedBody): ServerRequestInterface
    {
        $request = clone $this;

        $request->parsedBody = $this->filterParsedBody($parsedBody);

        return $request;
    }

    /**
     * Registers a callback to parse the specific content type.
     *
     * @param string $contentType
     * @param callable $callback
     *
     * @throws \InvalidArgumentException
     */
    public function registerContentTypeParser(string $contentType, callable $callback): void
    {
        $this->contentTypeParsers[$contentType] = $callback;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (!isset($this->attributes[$name])) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value): ServerRequestInterface
    {
        $request    = clone $this;
        $attributes = $this->attributes;

        $attributes[$name]   = $value;
        $request->attributes = $this->filterAttributes($attributes);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name): ServerRequestInterface
    {
        $attributes = $this->attributes;
        unset($attributes[$name]);

        $request = clone $this;

        $request->attributes = $this->filterAttributes($attributes);

        return $request;
    }

    /**
     * Filters query parameters to make sure they're valid.
     *
     * @param array $query
     *
     * @return array
     */
    private function filterQueryParams(array $query): array
    {
        $this->query = new ReadableArrayCollection($query);

        return $query;
    }

    /**
     * Filters attributes to make sure they're valid.
     *
     * @param array $attributes
     *
     * @return array
     */
    private function filterAttributes(array $attributes): array
    {
        return $attributes;
    }

    /**
     * Filters cookie parameters to make sure they're valid.
     *
     * @param array $cookies
     *
     * @return array
     */
    private function filterCookieParams(array $cookies): array
    {
        return $cookies;
    }

    /**
     * Filters file parameters to make sure they're valid.
     *
     * @param array $files Nested array of UploadedFileInterface|UploadedFileInterface[]
     *
     * @return UploadedFileInterface[]
     *
     * @throws \InvalidArgumentException
     */
    private function filterFileParams(array $files): array
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                $this->filterFileParams($file);
            } elseif (!$file instanceof UploadedFileInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Files can only contain instances of %s',
                        UploadedFileInterface::class
                    )
                );
            }
        }

        return $files;
    }

    /**
     * Filters server parameters to make sure they're valid.
     *
     * @param array $server
     *
     * @return array
     */
    private function filterServerParams(array $server): array
    {
        return $server;
    }

    /**
     * Filters parsed body to make sure it's valid.
     *
     * @param null|array|object $parsedBody
     *
     * @return null|array|object
     *
     * @throws \InvalidArgumentException
     */
    private function filterParsedBody($parsedBody)
    {
        if (!is_null($parsedBody) && !is_array($parsedBody) && !is_object($parsedBody)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Parsed body must be an array, object, or null; %s given.',
                    gettype($parsedBody)
                )
            );
        }

        if (is_array($parsedBody)) {
            $this->request = new ReadableArrayCollection($parsedBody);
        } else {
            $this->request = new ReadableArrayCollection([]);
        }

        return $parsedBody;
    }

    /**
     * Gets the content type of the request, if set.
     *
     * @return string
     */
    private function getContentType(): string
    {
        $contentTypes = $this->getHeader('Content-Type');
        $contentType  = reset($contentTypes);
        if (!$contentType) {
            return '';
        }

        return trim(explode(';', $contentType, 2)[0]);
    }
}
