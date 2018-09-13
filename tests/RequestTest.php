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
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Request();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestInterface::class, $this->fixture);
    }

    public function testGetRequestTarget()
    {
        $actual = $this->fixture->getRequestTarget();

        $this->assertEquals('/', $actual);
    }

    public function testGetRequestTargetWithQueryString()
    {
        $path  = uniqid('path');
        $query = uniqid('query');

        $this->fixture = new Request('GET', $path.'?'.$query);

        $actual = $this->fixture->getRequestTarget();

        $this->assertEquals('/'.$path.'?'.$query, $actual);
    }

    public function testWithRequestTarget()
    {
        $uri = uniqid('path').'?'.uniqid('query');

        $clone = $this->fixture->withRequestTarget($uri);
        $old   = $this->fixture->getRequestTarget();
        $new   = $clone->getRequestTarget();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('/', $old);
        $this->assertEquals($uri, $new);
    }

    public function testWithMethod()
    {
        $method = $this->getValidMethod();

        $clone = $this->fixture->withMethod($method);
        $old   = $this->fixture->getMethod();
        $new   = $clone->getMethod();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('GET', $old);
        $this->assertEquals($method, $new);
    }

    public function testWithMethodThrowsException()
    {
        $method = uniqid();

        $message = 'HTTP method "'.$method.'" is invalid. Valid methods are: '
            .'["OPTIONS", "HEAD", "GET", "POST", "PUT", "PATCH", "DELETE", "TRACE", "CONNECT"]';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withMethod($method);
    }

    /**
     * @dataProvider sampleUris
     */
    public function testWithUri($headers, $uri, $preserveHost, $expected)
    {
        $this->fixture = new Request('GET', '', $headers);

        $clone = $this->fixture->withUri(new Uri($uri), $preserveHost);
        $old   = $this->fixture->getUri();
        $new   = $clone->getUri();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('/', (string) $old);
        $this->assertEquals($uri, (string) $new);
        $this->assertEquals($expected, $clone->getHeaderLine('Host'));
    }

    public function sampleUris()
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

    /**
     * Gets a valid HTTP method.
     *
     * @return string
     */
    protected function getValidMethod()
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
