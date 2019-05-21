<?php

namespace Bitty\Tests\Http;

use Bitty\Http\RequestBody;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Psr\Http\Message\StreamInterface;

class RequestBodyTest extends TestCase
{
    /**
     * @var RequestBody
     */
    private $fixture = null;

    /**
     * @var vfsStreamDirectory
     */
    private $root = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();

        $this->fixture = new RequestBody();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(StreamInterface::class, $this->fixture);
    }

    public function testIsResource(): void
    {
        self::assertIsResource($this->fixture->detach());
    }

    public function testSourceCopiedToDest(): void
    {
        $content = uniqid();
        $source  = $this->root->url().'/'.uniqid('source');
        $dest    = $this->root->url().'/'.uniqid('dest');

        file_put_contents($source, $content);

        $fixture = new RequestBody($source, $dest);

        self::assertEquals($content, (string) $fixture);
        self::assertEquals($content, file_get_contents($dest));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSourceNotAvailable(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $content = uniqid();
        $source  = uniqid('source');
        $dest    = $this->root->url().'/'.uniqid('dest');
        vfsStream::newFile($source, 0000)->withContent($content)->at($this->root);

        $fixture = new RequestBody($this->root->url().'/'.$source, $dest);

        self::assertEquals('', (string) $fixture);

        error_reporting($level);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDestNotAvailable(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $source = $this->root->url().'/'.uniqid('source');
        $dest   = uniqid('dest');
        vfsStream::newDirectory($dest, 0400)->at($this->root);

        file_put_contents($source, uniqid());

        $message = 'Bitty\Http\Stream must be constructed with a resource or string; boolean given.';
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage($message);

        $fixture = new RequestBody($source, $this->root->url().'/'.$dest.'/'.uniqid());

        error_reporting($level);
    }
}
