<?php

namespace Bitty\Tests\Http;

use Bitty\Http\UploadedFileFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactoryTest extends TestCase
{
    /**
     * @var UploadedFileFactory
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new UploadedFileFactory();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $this->fixture);
    }

    public function testCreateUploadedFile()
    {
        $stream          = $this->createMock(StreamInterface::class);
        $size            = rand();
        $error           = \UPLOAD_ERR_OK;
        $clientFilename  = uniqid();
        $clientMediaType = uniqid();

        $actual = $this->fixture->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);

        $this->assertInstanceOf(UploadedFileInterface::class, $actual);
        $this->assertSame($stream, $actual->getStream());
        $this->assertEquals($size, $actual->getSize());
        $this->assertEquals($error, $actual->getError());
        $this->assertEquals($clientFilename, $actual->getClientFilename());
        $this->assertEquals($clientMediaType, $actual->getClientMediaType());
    }
}
