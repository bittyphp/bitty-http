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

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ServerRequestFactory();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $this->fixture);
    }

    public function testCreateServerRequest()
    {
        $method = 'GET';
        $uri    = uniqid('/');
        $params = [uniqid() => uniqid()];
        $actual = $this->fixture->createServerRequest($method, $uri, $params);

        $this->assertInstanceOf(ServerRequestInterface::class, $actual);
        $this->assertEquals($method, $actual->getMethod());
        $this->assertEquals($uri, $actual->getUri()->getPath());
        $this->assertEquals($params, $actual->getServerParams());
    }
}
