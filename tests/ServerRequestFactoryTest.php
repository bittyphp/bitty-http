<?php

namespace Bitty\Tests\Http;

use Bitty\Http\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactoryTest extends TestCase
{
    /**
     * @var ServerRequestFactory
     */
    protected $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new ServerRequestFactory();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(ServerRequestFactoryInterface::class, $this->fixture);
    }

    public function testCreateServerRequest(): void
    {
        $method = 'GET';
        $uri    = uniqid('/');
        $params = [uniqid() => uniqid()];
        $actual = $this->fixture->createServerRequest($method, $uri, $params);

        self::assertInstanceOf(ServerRequestInterface::class, $actual);
        self::assertEquals($method, $actual->getMethod());
        self::assertEquals($uri, $actual->getUri()->getPath());
        self::assertEquals($params, $actual->getServerParams());
    }
}
