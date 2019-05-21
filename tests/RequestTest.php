<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Request;
use Bitty\Http\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Request();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RequestInterface::class, $this->fixture);
    }

    public function testGetRequestTarget(): void
    {
        $actual = $this->fixture->getRequestTarget();

        self::assertEquals('/', $actual);
    }

    public function testGetRequestTargetWithQueryString(): void
    {
        $path  = uniqid('path');
        $query = uniqid('query');

        $this->fixture = new Request('GET', $path.'?'.$query);

        $actual = $this->fixture->getRequestTarget();

        self::assertEquals('/'.$path.'?'.$query, $actual);
    }

    public function testWithRequestTarget(): void
    {
        $uri = uniqid('path').'?'.uniqid('query');

        $clone = $this->fixture->withRequestTarget($uri);
        $old   = $this->fixture->getRequestTarget();
        $new   = $clone->getRequestTarget();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('/', $old);
        self::assertEquals($uri, $new);
    }

    public function testWithMethod(): void
    {
        $method = $this->getValidMethod();

        $clone = $this->fixture->withMethod($method);
        $old   = $this->fixture->getMethod();
        $new   = $clone->getMethod();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('GET', $old);
        self::assertEquals($method, $new);
    }

    public function testWithNonUppercaseMethod(): void
    {
        $method = strtolower($this->getValidMethod());

        $clone = $this->fixture->withMethod($method);
        $old   = $this->fixture->getMethod();
        $new   = $clone->getMethod();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('GET', $old);
        self::assertEquals($method, $new);
    }

    public function testWithMethodThrowsException(): void
    {
        $method = uniqid();

        $message = 'HTTP method "'.$method.'" is invalid. Valid methods are: '
            .'["OPTIONS", "HEAD", "GET", "POST", "PUT", "PATCH", "DELETE", "TRACE", "CONNECT"]';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withMethod($method);
    }

    /**
     * @param array $headers
     * @param string $uri
     * @param bool $preserveHost
     * @param string $expected
     *
     * @dataProvider sampleUris
     */
    public function testWithUri(
        array $headers,
        string $uri,
        bool $preserveHost,
        string $expected
    ): void {
        $this->fixture = new Request('GET', '', $headers);

        $clone = $this->fixture->withUri(new Uri($uri), $preserveHost);
        $old   = $this->fixture->getUri();
        $new   = $clone->getUri();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('/', (string) $old);
        self::assertEquals($uri, (string) $new);
        self::assertEquals($expected, $clone->getHeaderLine('Host'));
    }

    public function sampleUris(): array
    {
        $host = uniqid('host');

        return [
            'nothing set' => [
                'headers' => [],
                'uri' => '/',
                'preserveHost' => false,
                'expected' => '',
            ],
            'preserved, host and header set' => [
                'headers' => ['Host' => $host],
                'uri' => 'http://example.com/',
                'preserveHost' => true,
                'expected' => $host,
            ],
            'preserved, host set' => [
                'headers' => [],
                'uri' => 'http://example.com/',
                'preserveHost' => true,
                'expected' => 'example.com',
            ],
            'preserved, header set' => [
                'headers' => ['Host' => $host],
                'uri' => '/',
                'preserveHost' => true,
                'expected' => $host,
            ],
            'not preserved, host set' => [
                'headers' => ['Host' => $host],
                'uri' => 'http://example.com/',
                'preserveHost' => false,
                'expected' => 'example.com',
            ],
            'not preserved, host not set' => [
                'headers' => ['Host' => $host],
                'uri' => '/',
                'preserveHost' => false,
                'expected' => $host,
            ],
        ];
    }

    public function testWithUriPreservesHost(): void
    {
        $new = 'http://'.uniqid('new').'.com/'.uniqid();

        $fixture = (new Request())->withHeader('Host', uniqid());
        $clone   = $fixture->withUri(new Uri($new));

        self::assertNotEquals($fixture->getHeaderLine('Host'), $clone->getHeaderLine('Host'));
    }

    public function testClone(): void
    {
        $fixture = new Request('GET', uniqid(), [], uniqid());
        $clone   = clone $fixture;

        self::assertEquals((string) $fixture->getBody(), (string) $clone->getBody());
    }

    /**
     * Gets a valid HTTP method.
     *
     * @return string
     */
    private function getValidMethod()
    {
        $validMethods = [
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

        $key = array_rand($validMethods);

        return $validMethods[$key];
    }
}
