<?php

namespace Bitty\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * Default ports.
     *
     * @var int[]
     */
    protected $defaultPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp' => 21,
    ];

    /**
     * URI scheme.
     *
     * @var string
     */
    protected $scheme = null;

    /**
     * Authority username.
     *
     * @var string|null
     */
    protected $user = null;

    /**
     * Authority password.
     *
     * @var string|null
     */
    protected $pass = null;

    /**
     * URI hostname.
     *
     * @var string
     */
    protected $host = null;

    /**
     * URI port number.
     *
     * @var int
     */
    protected $port = null;

    /**
     * URI path.
     *
     * @var string
     */
    protected $path = null;

    /**
     * URI query string.
     *
     * @var string
     */
    protected $query = null;

    /**
     * URI fragment.
     *
     * @var string
     */
    protected $fragment = null;

    /**
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        $data = parse_url($uri);
        if (false === $data) {
            return;
        }

        $data += [
            'scheme' => '',
            'host' => '',
            'port' => '',
            'path' => '',
            'query' => '',
            'fragment' => '',
            'user' => '',
            'pass' => '',
        ];

        $this->scheme   = $this->filterScheme($data['scheme']);
        $this->host     = $this->filterHost($data['host']);
        $this->port     = $this->filterPort((int) $data['port']);
        $this->path     = $this->filterPath($data['path']);
        $this->query    = $this->filterQuery($data['query']);
        $this->fragment = $this->filterFragment($data['fragment']);
        $this->user     = (string) $data['user'];
        $this->pass     = (string) $data['pass'];
    }

    /**
     * Creates a new URI from an array.
     *
     * The input array is expected to match the format of $_SERVER.
     *
     * @param array $server
     *
     * @return UriInterface
     */
    public static function createFromArray(array $server): UriInterface
    {
        $scheme  = 'http';
        $isHttps = empty($server['HTTPS']) ? null : $server['HTTPS'];
        if (!empty($isHttps) && 'off' !== strtolower($isHttps)) {
            $scheme = 'https';
        }

        $user = empty($server['PHP_AUTH_USER']) ? '' : $server['PHP_AUTH_USER'];
        $pass = empty($server['PHP_AUTH_PW']) ? null : $server['PHP_AUTH_PW'];
        $port = empty($server['SERVER_PORT']) ? '' : $server['SERVER_PORT'];

        if (!empty($server['HTTP_HOST'])) {
            $host = $server['HTTP_HOST'];
            $pos  = strrpos($host, ':');
            if (false !== $pos) {
                $port = substr($host, $pos + 1);
                $host = substr($host, 0, $pos);
            }
        } else {
            $host = empty($server['SERVER_NAME']) ? '' : $server['SERVER_NAME'];
        }

        $uri = empty($server['REQUEST_URI']) ? '' : $server['REQUEST_URI'];

        $path = parse_url($uri, PHP_URL_PATH);
        if (empty($path)) {
            $path = empty($server['PATH_INFO']) ? '' : $server['PATH_INFO'];
        }

        $query = parse_url($uri, PHP_URL_QUERY);
        if (empty($query)) {
            $query = empty($server['QUERY_STRING']) ? '' : $server['QUERY_STRING'];
        }

        $uri = new static();

        return $uri->withScheme($scheme)
            ->withUserInfo($user, $pass)
            ->withHost($host)
            ->withPort($port)
            ->withPath($path)
            ->withQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $string = '';

        $scheme = $this->getScheme();
        if ('' !== $scheme) {
            $string .= $scheme.':';
        }

        $authority = $this->getAuthority();
        if (!empty($authority) || 'file' === $this->scheme) {
            // 'file' is special and doesn't need a host
            $string .= '//'.$authority;
        }

        $string .= '/'.ltrim($this->getPath(), '/');

        $query = $this->getQuery();
        if ('' !== $query) {
            $string .= '?'.$query;
        }

        $fragment = $this->getFragment();
        if ('' !== $fragment) {
            $string .= '#'.$fragment;
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme(): string
    {
        if (empty($this->scheme)) {
            return '';
        }

        return $this->scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority(): string
    {
        $string = '';

        $userInfo = $this->getUserInfo();
        if (!empty($userInfo)) {
            $string .= $userInfo.'@';
        }

        if (!empty($this->host)) {
            $string .= $this->host;
        } elseif ('http' === $this->scheme || 'https' === $this->scheme) {
            $string .= 'localhost';
        }

        $port = $this->getPort();
        if (null !== $port) {
            $string .= ':'.$port;
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo(): string
    {
        if (empty($this->user)) {
            return '';
        }

        $string = $this->user;
        if (!empty($this->pass)) {
            $string .= ':'.$this->pass;
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): string
    {
        if (empty($this->host)) {
            return '';
        }

        return $this->host;
    }

    /**
     * {@inheritDoc}
     */
    public function getPort(): ?int
    {
        if (empty($this->port)) {
            return null;
        }

        if (isset($this->defaultPorts[$this->scheme])
            && $this->defaultPorts[$this->scheme] === $this->port
        ) {
            return null;
        }

        return $this->port;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        if (empty($this->path)) {
            return '';
        }

        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): string
    {
        if (empty($this->query)) {
            return '';
        }

        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment(): string
    {
        if (empty($this->fragment)) {
            return '';
        }

        return $this->fragment;
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme($scheme): UriInterface
    {
        $uri = clone $this;

        $uri->scheme = $this->filterScheme($scheme);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        $uri = clone $this;

        $uri->user = $user;
        $uri->pass = $password;

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withHost($host): UriInterface
    {
        $uri = clone $this;

        $uri->host = $this->filterHost($host);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withPort($port): UriInterface
    {
        $uri = clone $this;

        $uri->port = $this->filterPort((int) $port);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath($path): UriInterface
    {
        $uri = clone $this;

        $uri->path = $this->filterPath($path);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery($query): UriInterface
    {
        $uri = clone $this;

        $uri->query = $this->filterQuery($query);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment($fragment): UriInterface
    {
        $uri = clone $this;

        $uri->fragment = $this->filterFragment($fragment);

        return $uri;
    }

    /**
     * Filters a scheme to make sure it's valid.
     *
     * @param string $scheme
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterScheme(string $scheme): string
    {
        $scheme = rtrim(strtolower($scheme), ':');
        if (!empty($scheme) && !preg_match('/^[a-z][a-z0-9\+\.-]*$/', $scheme)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid scheme "%s".', $scheme)
            );
        }

        return $scheme;
    }

    /**
     * Filters a host to make sure it's valid.
     *
     * @param string $host
     *
     * @return string
     */
    protected function filterHost(string $host): string
    {
        return strtolower($host);
    }

    /**
     * Filters a port to make sure it's valid.
     *
     * @param int $port
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function filterPort(int $port): int
    {
        // allow zero as an empty check
        if (0 > $port || 65535 < $port) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid port %d. Must be between 1 and 65,535.',
                    $port
                )
            );
        }

        return $port;
    }

    /**
     * Filters a path to make sure it's valid.
     *
     * @param string|null $path
     *
     * @return string
     */
    protected function filterPath(?string $path): string
    {
        if (!$path) {
            return '';
        }

        return implode(
            '/',
            array_map(
                'rawurlencode',
                array_map(
                    'rawurldecode',
                    explode('/', $path)
                )
            )
        );
    }

    /**
     * Filters a query string to make sure it's valid.
     *
     * @param string|null $query
     *
     * @return string
     */
    protected function filterQuery(?string $query): string
    {
        if (!$query) {
            return '';
        }

        $params = explode('&', ltrim($query, '?'));

        $len = count($params);
        for ($i = 0; $i < $len; $i++) {
            if (empty($params[$i])) {
                continue;
            }

            $params[$i] = implode(
                '=',
                array_map(
                    'rawurlencode',
                    array_map(
                        'rawurldecode',
                        explode('=', $params[$i], 2)
                    )
                )
            );
        }

        return implode('&', $params);
    }

    /**
     * Filters a fragment to make sure it's valid.
     *
     * @param string|null $fragment
     *
     * @return string
     */
    protected function filterFragment(?string $fragment): string
    {
        if (!$fragment) {
            return '';
        }

        return rawurlencode(
            rawurldecode(
                ltrim($fragment, '#')
            )
        );
    }
}
