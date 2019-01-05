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

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new UploadedFileFactory();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(UploadedFileFactoryInterface::class, $this->fixture);
    }

    public function testCreateUploadedFile(): void
    {
        $stream          = $this->createMock(StreamInterface::class);
        $size            = rand();
        $error           = \UPLOAD_ERR_OK;
        $clientFilename  = uniqid();
        $clientMediaType = uniqid();

        $actual = $this->fixture->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);

        self::assertInstanceOf(UploadedFileInterface::class, $actual);
        self::assertSame($stream, $actual->getStream());
        self::assertEquals($size, $actual->getSize());
        self::assertEquals($error, $actual->getError());
        self::assertEquals($clientFilename, $actual->getClientFilename());
        self::assertEquals($clientMediaType, $actual->getClientMediaType());
    }
}
