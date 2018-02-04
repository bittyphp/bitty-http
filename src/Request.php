<?php

namespace Bitty\Http;

use Bitty\Http\AbstractMessage;
use Bitty\Http\Headers;
use Bitty\Http\RequestBody;
use Bitty\Http\UploadedFiles;
use Bitty\Http\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class Request extends AbstractMessage implements ServerRequestInterface
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
     * Updated 2017-12-22
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
     * Query parameters.
     *
     * @var string[]
     */
    protected $query = null;

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
     * @var callback[]
     */
    protected $contentTypeParsers = [];

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array $headers
     * @param array $query
     * @param array $request
     * @param array $cookies
     * @param UploadedFileInterface[] $files
     * @param array $server
     * @param array $attributes
     * @param StreamInterface|resource|string $body
     */
    public function __construct(
        $method = 'GET',
        $uri = '',
        array $headers = [],
        array $query = [],
        array $request = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        array $attributes = [],
        $body = ''
    ) {
        $this->method     = $this->filterMethod($method);
        $this->uri        = new Uri((string) $uri);
        $this->headers    = $this->filterHeaders($headers);
        $this->query      = $this->filterQueryParams($query);
        $this->cookies    = $this->filterCookieParams($cookies);
        $this->files      = $this->filterFileParams($files);
        $this->server     = $this->filterServerParams($server);
        $this->attributes = $this->filterAttributes($attributes);
        $this->body       = $this->filterBody($body);

        $protocol = empty($this->server['SERVER_PROTOCOL']) ?
            '1.1' :
            $this->server['SERVER_PROTOCOL'];

        $this->protocolVersion = $this->filterProtocolVersion(
            str_replace('HTTP/', '', $protocol)
        );

        $contentTypes = $this->getHeader('Content-Type');

        if ('POST' === $this->method
            && (
                in_array('application/x-www-form-urlencoded', $contentTypes)
                || in_array('multipart/form-data', $contentTypes)
            )
        ) {
            $this->parsedBody = $request;
        }

        $this->registerContentTypeParser('application/json', function ($body) {
            $json = json_decode($body, true);
            if (!is_array($json)) {
                return null;
            }

            return $json;
        });

        $this->registerContentTypeParser('application/x-www-form-urlencoded', function ($body) {
            parse_str($body, $data);

            return $data;
        });
    }

    public function __clone()
    {
        $this->uri  = clone $this->uri;
        $this->body = clone $this->body;
    }

    /**
     * Creates a new request from global variables.
     *
     * @return static
     */
    public static function createFromGlobals()
    {
        $server  = new Headers();
        $headers = $server->getHeaders($_SERVER);
        $method  = empty($headers['REQUEST_METHOD']) ? 'GET' : $headers['REQUEST_METHOD'];
        $uri     = Uri::createFromArray($_SERVER);
        $body    = new RequestBody();
        $files   = new UploadedFiles();

        return new static(
            $method,
            $uri,
            $headers,
            $_GET,
            $_POST,
            $_COOKIE,
            $files->collapseFileTree($_FILES),
            $_SERVER,
            [],
            $body
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget()
    {
        if (null === $this->requestTarget) {
            $string = '/'.ltrim($this->uri->getPath(), '/');

            $query = $this->uri->getQuery();
            if ('' !== $query) {
                $string .= '?'.$query;
            }

            return $string;
        }

        return $this->requestTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget)
    {
        $request = clone $this;

        $request->requestTarget = $this->filterRequestTarget($requestTarget);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method)
    {
        $request = clone $this;

        $request->method = $this->filterMethod($method);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri()
    {
        return clone $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $request = clone $this;

        $request->uri = $uri;

        if ($preserveHost) {
            if ('' === $this->getHeaderLine('Host') && '' !== $uri->getHost()) {
                return $request->withHeader('Host', $uri->getHost());
            }
        } elseif ('' !== $uri->getHost()) {
            return $request->withHeader('Host', $uri->getHost());
        }

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams()
    {
        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query)
    {
        $request = clone $this;

        $request->query = $this->filterQueryParams($query);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams()
    {
        return $this->cookies;
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies)
    {
        $request = clone $this;

        $request->cookies = $this->filterCookieParams($cookies);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles()
    {
        return $this->files;
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $files)
    {
        $request = clone $this;

        $request->files = $this->filterFileParams($files);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getServerParams()
    {
        return $this->server;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        if (null === $this->parsedBody) {
            $body = (string) $this->body;

            $contentTypes = $this->getHeader('Content-Type');
            foreach ($contentTypes as $contentType) {
                if (!isset($this->contentTypeParsers[$contentType])) {
                    continue;
                }

                $this->parsedBody = $this->filterParsedBody(
                    $this->contentTypeParsers[$contentType]($body)
                );

                return $this->parsedBody;
            }
        }

        return $this->parsedBody;
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($parsedBody)
    {
        $request = clone $this;

        $request->parsedBody = $this->filterParsedBody($parsedBody);

        return $request;
    }

    /**
     * Registers a callback to parse the specific content type.
     *
     * @param string $contentType
     * @param callback $callback
     */
    public function registerContentTypeParser($contentType, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Callback for "%s" must be a callable; %s given.',
                    $contentType,
                    gettype($callback)
                )
            );
        }

        $this->contentTypeParsers[(string) $contentType] = $callback;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes()
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
    public function withAttribute($name, $value)
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
    public function withoutAttribute($name)
    {
        $attributes = $this->attributes;
        unset($attributes[$name]);

        $request = clone $this;

        $request->attributes = $this->filterAttributes($attributes);

        return $request;
    }

    /**
     * Filters HTTP method to make sure it's valid.
     *
     * @param string $method
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterMethod($method)
    {
        if (!in_array($method, $this->validMethods)) {
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
    protected function filterRequestTarget($requestTarget)
    {
        return (string) $requestTarget;
    }

    /**
     * Filters query parameters to make sure they're valid.
     *
     * @param array $query
     *
     * @return array
     */
    protected function filterQueryParams(array $query)
    {
        return $query;
    }

    /**
     * Filters attributes to make sure they're valid.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function filterAttributes(array $attributes)
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
    protected function filterCookieParams(array $cookies)
    {
        return $cookies;
    }

    /**
     * Filters file parameters to make sure they're valid.
     *
     * @param UploadedFileInterface[] $files
     *
     * @return UploadedFileInterface[]
     */
    protected function filterFileParams(array $files)
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
    protected function filterServerParams(array $server)
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
    protected function filterParsedBody($parsedBody)
    {
        if (!is_null($parsedBody) && !is_array($parsedBody) && !is_object($parsedBody)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Parsed body must be an array, object, or null; %s given.',
                    gettype($parsedBody)
                )
            );
        }

        return $parsedBody;
    }
}
