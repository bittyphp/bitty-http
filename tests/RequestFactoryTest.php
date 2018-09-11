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
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new RequestFactory();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestFactoryInterface::class, $this->fixture);
    }

    public function testCreateRequest()
    {
        $method = 'GET';
        $uri    = uniqid('/');
        $actual = $this->fixture->createRequest($method, $uri);

        $this->assertInstanceOf(RequestInterface::class, $actual);
        $this->assertEquals($method, $actual->getMethod());
        $this->assertEquals($uri, $actual->getUri()->getPath());
    }
}
