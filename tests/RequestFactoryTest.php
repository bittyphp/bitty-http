<?php

namespace Bitty\Tests\Http;

use Bitty\Http\RequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RequestFactoryTest extends TestCase
{
    /**
     * @var RequestFactory
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new RequestFactory();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(RequestFactoryInterface::class, $this->fixture);
    }

    public function testCreateRequest(): void
    {
        $method = 'GET';
        $uri    = uniqid('/');
        $actual = $this->fixture->createRequest($method, $uri);

        self::assertInstanceOf(RequestInterface::class, $actual);
        self::assertEquals($method, $actual->getMethod());
        self::assertEquals($uri, $actual->getUri()->getPath());
    }
}
