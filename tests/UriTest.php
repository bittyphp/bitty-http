<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UriTest extends TestCase
{
    /**
     * @var Uri
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Uri();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(UriInterface::class, $this->fixture);
    }

    /**
     * @param string $uri
     * @param array $expected
     *
     * @dataProvider sampleUriData
     */
    public function testUriData(string $uri, array $expected): void
    {
        $fixture = new Uri($uri);

        $actual = [
            'scheme' => $fixture->getScheme(),
            'userInfo' => $fixture->getUserInfo(),
            'host' => $fixture->getHost(),
            'port' => $fixture->getPort(),
            'path' => $fixture->getPath(),
            'query' => $fixture->getQuery(),
            'fragment' => $fixture->getFragment(),
            'authority' => $fixture->getAuthority(),
        ];

        self::assertEquals($expected, $actual);
    }

    public function sampleUriData(): array
    {
        return [
            'full uri' => [
                'uri' => 'http://user:pass@eXaMpLe.com:1234/path/to/file?foo=bar#baz',
                'expected' => [
                    'scheme' => 'http',
                    'userInfo' => 'user:pass',
                    'host' => 'example.com',
                    'port' => 1234,
                    'path' => '/path/to/file',
                    'query' => 'foo=bar',
                    'fragment' => 'baz',
                    'authority' => 'user:pass@example.com:1234',
                ],
            ],
            'no scheme' => [
                'uri' => '//eXaMpLe.com/path/to/file?foo=bar#baz',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/path/to/file',
                    'query' => 'foo=bar',
                    'fragment' => 'baz',
                    'authority' => 'example.com',
                ],
            ],
            'no host' => [
                'uri' => '/path/to/file?foo=bar#baz',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => '',
                    'port' => null,
                    'path' => '/path/to/file',
                    'query' => 'foo=bar',
                    'fragment' => 'baz',
                    'authority' => '',
                ],
            ],
            'query string only' => [
                'uri' => '?foo=bar',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => '',
                    'port' => null,
                    'path' => '',
                    'query' => 'foo=bar',
                    'fragment' => '',
                    'authority' => '',
                ],
            ],
            'host only' => [
                'uri' => '//example.com',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                    'authority' => 'example.com',
                ],
            ],
            'high port' => [
                'uri' => '//example.com:65535',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => 'example.com',
                    'port' => 65535,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                    'authority' => 'example.com:65535',
                ],
            ],
            'low port' => [
                'uri' => '//example.com:1',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => 'example.com',
                    'port' => 1,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                    'authority' => 'example.com:1',
                ],
            ],
            'failure' => [
                'uri' => 'http:///example.com',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => '',
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                    'authority' => '',
                ],
            ],
        ];
    }

    public function testGetAuthorityUserInfoWithoutHost(): void
    {
        $user = uniqid();
        $pass = uniqid();

        $uri = Uri::createFromArray(
            [
                'PHP_AUTH_USER' => $user,
                'PHP_AUTH_PW' => $pass,
            ]
        );

        $actual = $uri->getAuthority();

        self::assertEquals($user.':'.$pass.'@localhost', $actual);
    }

    /**
     * @param string $uri
     * @param string $expected
     *
     * @dataProvider sampleUriStringData
     */
    public function testToString(string $uri, string $expected): void
    {
        $fixture = new Uri($uri);

        self::assertEquals($expected, (string) $fixture);
    }

    public function sampleUriStringData(): array
    {
        return [
            'blank' => [
                'uri' => '',
                'expected' => '/',
            ],
            'file, no host' => [
                'uri' => 'file:///path/to/file',
                'expected' => 'file:///path/to/file',
            ],
            'http, no host' => [
                'uri' => 'http:/path/to/file',
                'expected' => 'http://localhost/path/to/file',
            ],
            'https, no host' => [
                'uri' => 'https:/path/to/file',
                'expected' => 'https://localhost/path/to/file',
            ],
            'http, default port' => [
                'uri' => 'http://example.com:80/',
                'expected' => 'http://example.com/',
            ],
            'http, non-default port' => [
                'uri' => 'http://example.com:443/',
                'expected' => 'http://example.com:443/',
            ],
            'https, default port' => [
                'uri' => 'https://example.com:443/',
                'expected' => 'https://example.com/',
            ],
            'https, non-default port' => [
                'uri' => 'https://example.com:80/',
                'expected' => 'https://example.com:80/',
            ],
            'user only' => [
                'uri' => 'ftp://user@example.com/',
                'expected' => 'ftp://user@example.com/',
            ],
            'user and pass' => [
                'uri' => 'ftp://user:pass@example.com:21/',
                'expected' => 'ftp://user:pass@example.com/',
            ],
            'query and fragment' => [
                'uri' => '/path/to/file?foo=bar#baz',
                'expected' => '/path/to/file?foo=bar#baz',
            ],
        ];
    }

    public function testWithScheme(): void
    {
        $scheme = uniqid('a+.-');

        $clone = $this->fixture->withScheme(strtoupper($scheme).':');

        $old = $this->fixture->getScheme();
        $new = $clone->getScheme();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old);
        self::assertEquals($scheme, $new);
    }

    public function testWithSchemeThrowsException(): void
    {
        $scheme = uniqid();

        $message = 'Invalid scheme "'.$scheme.'".';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withScheme($scheme);
    }

    public function testWithUserInfoNoPass(): void
    {
        $user = uniqid('user');

        $clone = $this->fixture->withUserInfo($user);

        $old = $this->fixture->getUserInfo();
        $new = $clone->getUserInfo();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old);
        self::assertEquals($user, $new);
    }

    public function testWithUserInfo(): void
    {
        $user = uniqid('user');
        $pass = uniqid('pass');

        $clone = $this->fixture->withUserInfo($user, $pass);

        $old = $this->fixture->getUserInfo();
        $new = $clone->getUserInfo();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old);
        self::assertEquals($user.':'.$pass, $new);
    }

    public function testWithHost(): void
    {
        $host = uniqid('host');

        $clone = $this->fixture->withHost(strtoupper($host));

        $old = $this->fixture->getHost();
        $new = $clone->getHost();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old);
        self::assertEquals($host, $new);
    }

    public function testWithPort(): void
    {
        $port = rand(0, 65535);

        $clone = $this->fixture->withPort($port);

        $old = $this->fixture->getPort();
        $new = $clone->getPort();

        self::assertNotSame($this->fixture, $clone);
        self::assertNull($old);
        self::assertEquals($port, $new);
    }

    /**
     * @param int $port
     *
     * @dataProvider sampleInvalidPorts
     */
    public function testWithPortThrowsException(int $port): void
    {
        $message = 'Invalid port '.$port.'. Must be between 1 and 65,535.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withPort($port);
    }

    public function sampleInvalidPorts(): array
    {
        return [
            'low limit' => [-1],
            'high limit' => [65536],
            'too high' => [rand(65536, 99999)],
            'too low' => [-rand(1, 99999)],
        ];
    }

    /**
     * @param string $path
     * @param string $expected
     *
     * @dataProvider samplePaths
     */
    public function testWithPath(string $path, string $expected): void
    {
        $clone = $this->fixture->withPath($path);

        $old = $this->fixture->getPath();
        $new = $clone->getPath();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old);
        self::assertEquals($expected, $new);
    }

    public function samplePaths(): array
    {
        $path = uniqid('path');

        return [
            'empty path' => [
                'path' => '',
                'expected' => '',
            ],
            'normal path' => [
                'path' => $path,
                'expected' => $path,
            ],
            'invalid characters encoded' => [
                'path' => $path.'/^'.$path,
                'expected' => $path.'/%5E'.$path,
            ],
            'encoded characters not double encoded' => [
                'path' => $path.'/%5E'.$path,
                'expected' => $path.'/%5E'.$path,
            ],
        ];
    }

    /**
     * @param string $query
     * @param string $expected
     *
     * @dataProvider sampleQueries
     */
    public function testWithQuery(string $query, string $expected): void
    {
        $clone = $this->fixture->withQuery($query);

        $old = $this->fixture->getQuery();
        $new = $clone->getQuery();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old);
        self::assertEquals($expected, $new);
    }

    public function sampleQueries(): array
    {
        $keyA   = uniqid('key');
        $keyB   = uniqid('key');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        return [
            'empty query' => [
                'query' => '',
                'expected' => '',
            ],
            'leading ? removed' => [
                'query' => '?'.$keyA.'='.$valueA,
                'expected' => $keyA.'='.$valueA,
            ],
            'normal query' => [
                'query' => $keyA.'='.$valueA.'&'.$keyB.'='.$valueB,
                'expected' => $keyA.'='.$valueA.'&'.$keyB.'='.$valueB,
            ],
            'extra =' => [
                'query' => $keyA.'=='.$valueA,
                'expected' => $keyA.'=%3D'.$valueA,
            ],
            'extra & ignored' => [
                'query' => $keyA.'='.$valueA.'&&'.$keyB.'='.$valueB,
                'expected' => $keyA.'='.$valueA.'&&'.$keyB.'='.$valueB,
            ],
            'invalid characters encoded' => [
                'query' => $keyA.'^='.$valueA.'&'.$keyB.'=^'.$valueB,
                'expected' => $keyA.'%5E='.$valueA.'&'.$keyB.'=%5E'.$valueB,
            ],
            'encoded characters not double encoded' => [
                'query' => $keyA.'%5E='.$valueA.'&'.$keyB.'=%5E'.$valueB,
                'expected' => $keyA.'%5E='.$valueA.'&'.$keyB.'=%5E'.$valueB,
            ],
        ];
    }

    /**
     * @param string $fragment
     * @param string $expected
     *
     * @dataProvider sampleFragments
     */
    public function testWithFragment(string $fragment, string $expected): void
    {
        $clone = $this->fixture->withFragment($fragment);

        $old = $this->fixture->getFragment();
        $new = $clone->getFragment();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old);
        self::assertEquals($expected, $new);
    }

    public function sampleFragments(): array
    {
        $value = uniqid('value');

        return [
            'empty fragment' => [
                'fragment' => '',
                'expected' => '',
            ],
            'leading # removed' => [
                'fragment' => '#'.$value,
                'expected' => $value,
            ],
            'normal fragment' => [
                'fragment' => $value,
                'expected' => $value,
            ],
            'invalid characters encoded' => [
                'fragment' => $value.'^'.$value,
                'expected' => $value.'%5E'.$value,
            ],
            'encoded characters not double encoded' => [
                'fragment' => $value.'%5E'.$value,
                'expected' => $value.'%5E'.$value,
            ],
        ];
    }

    /**
     * @param mixed[] $data
     * @param string $expected
     *
     * @dataProvider sampleEnvironmentData
     */
    public function testCreateFromArray(array $data, string $expected): void
    {
        $uri = Uri::createFromArray($data);

        self::assertEquals($expected, (string) $uri);
    }

    public function sampleEnvironmentData(): array
    {
        $user  = uniqid('user');
        $pass  = uniqid('pass');
        $host  = uniqid('host');
        $port  = rand(444, 65535);
        $path  = uniqid('path');
        $query = uniqid('query');

        return [
            'no data' => [
                'data' => [],
                'expected' => 'http://localhost/',
            ],
            'standard data' => [
                'data' => [
                    'PHP_AUTH_USER' => $user,
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'https on' => [
                'data' => [
                    'HTTPS' => uniqid(),
                    'PHP_AUTH_USER' => $user,
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'https://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'https off' => [
                'data' => [
                    'HTTPS' => 'off',
                    'PHP_AUTH_USER' => $user,
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'no password' => [
                'data' => [
                    'PHP_AUTH_USER' => $user,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$user.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'no user, with pass' => [
                'data' => [
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'port set from host header' => [
                'data' => [
                    'HTTP_HOST' => $host.':'.$port,
                    'SERVER_PORT' => rand(),
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'host set from server name' => [
                'data' => [
                    'SERVER_NAME' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'path set from path info' => [
                'data' => [
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'PATH_INFO' => $path,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path,
            ],
            'query set from query string' => [
                'data' => [
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'QUERY_STRING' => $query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/?'.$query,
            ],
        ];
    }
}
