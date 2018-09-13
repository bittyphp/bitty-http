<?php

namespace Bitty\Tests\Http;

use Bitty\Http\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactoryTest extends TestCase
{
    /**
     * @var StreamFactory
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new StreamFactory();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(StreamFactoryInterface::class, $this->fixture);
    }

    public function testCreateStream()
    {
        $message = uniqid();
        $actual  = $this->fixture->createStream($message);

        $this->assertInstanceOf(StreamInterface::class, $actual);
        $this->assertEquals($message, (string) $actual);
    }

    public function testCreateStreamFromFile()
    {
        $filename = __DIR__.'/bootstrap.php';
        $actual   = $this->fixture->createStreamFromFile($filename, 'r');

        $this->assertInstanceOf(StreamInterface::class, $actual);
        $this->assertEquals(file_get_contents($filename), (string) $actual);
    }

    public function testCreateStreamFromResource()
    {
        $filename = __DIR__.'/bootstrap.php';
        $resource = fopen($filename, 'r');
        $actual   = $this->fixture->createStreamFromResource($resource);

        $this->assertInstanceOf(StreamInterface::class, $actual);
        $this->assertEquals(file_get_contents($filename), (string) $actual);
    }
}
