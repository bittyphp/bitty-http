<?php

namespace Bitty\Tests\Http;

use Bitty\Http\UriFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactoryTest extends TestCase
{
    /**
     * @var UriFactory
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new UriFactory();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(UriFactoryInterface::class, $this->fixture);
    }

    public function testCreateUri()
    {
        $uri    = uniqid('/');
        $actual = $this->fixture->createUri($uri);

        $this->assertInstanceOf(UriInterface::class, $actual);
        $this->assertEquals($uri, $actual->getPath());
    }
}
