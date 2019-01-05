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

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new UriFactory();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(UriFactoryInterface::class, $this->fixture);
    }

    public function testCreateUri(): void
    {
        $uri    = uniqid('/');
        $actual = $this->fixture->createUri($uri);

        self::assertInstanceOf(UriInterface::class, $actual);
        self::assertEquals($uri, $actual->getPath());
    }
}
