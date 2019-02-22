<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Stream;
use Bitty\Http\UploadedFile;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();
    }

    public function testInstanceOf(): void
    {
        $fixture = new UploadedFile(uniqid());

        self::assertInstanceOf(UploadedFileInterface::class, $fixture);
    }

    public function testGetStream(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $fixture = new UploadedFile($stream);
        $actual  = $fixture->getStream();

        self::assertSame($stream, $actual);
    }

    public function testGetStreamLazyOpen(): void
    {
        $data = uniqid();
        $file = $this->root->url().'/'.uniqid();
        file_put_contents($file, $data);

        $fixture = new UploadedFile($file);
        $actual  = $fixture->getStream();

        self::assertInstanceOf(StreamInterface::class, $actual);
        self::assertEquals($data, (string) $actual);
    }

    public function testMoveTo(): void
    {
        $data  = uniqid();
        $fileA = $this->root->url().'/'.uniqid();
        $fileB = $this->root->url().'/'.uniqid();
        file_put_contents($fileA, $data);

        $fixture = new UploadedFile($fileA, null, null, null, rand(), 'cli');
        $fixture->moveTo($fileB);

        $actual = file_get_contents($fileB);

        self::assertEquals($data, $actual);
    }

    public function testGetStreamThrowsExceptionWhenAlreadyMoved(): void
    {
        $fileA = $this->root->url().'/'.uniqid();
        $fileB = $this->root->url().'/'.uniqid();
        file_put_contents($fileA, uniqid());

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Stream not available; the file appears to have been moved.');

        $fixture = new UploadedFile($fileA, null, null, null, rand(), 'cli');
        $fixture->moveTo($fileB);
        $fixture->getStream();
    }

    public function testMoveToThrowsExceptionWhenAlreadyMoved(): void
    {
        $fileA = $this->root->url().'/'.uniqid();
        $fileB = $this->root->url().'/'.uniqid();
        file_put_contents($fileA, uniqid());

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Unable to perform move; the file has already been moved.');

        $fixture = new UploadedFile($fileA, null, null, null, rand(), 'cli');
        $fixture->moveTo($fileB);
        $fixture->moveTo(uniqid());
    }

    public function testMoveToThrowsExceptionWhenSourceNotReadable(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $fileA = $this->root->url().'/'.uniqid();
        $fileB = $this->root->url().'/'.uniqid();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Failed to move file to "'.$fileB.'".');

        $fixture = new UploadedFile($fileA, null, null, null, rand(), 'cli');
        $fixture->moveTo($fileB);

        error_reporting($level);
    }

    public function testMoveToThrowsExceptionWhenNotWritable(): void
    {
        $fileA = $this->root->url().'/'.uniqid();
        $fileB = $this->root->url().'/'.uniqid();
        file_put_contents($fileA, uniqid());
        touch($fileB);
        chmod(dirname($fileB), 0500);

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Target path "'.$fileB.'" is not writable!');

        $fixture = new UploadedFile($fileA);
        $fixture->moveTo($fileB);
    }

    public function testMoveToThrowsExceptionWhenNotUploadedFile(): void
    {
        $fileA = $this->root->url().'/'.uniqid();
        $fileB = $this->root->url().'/'.uniqid();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('File is not a valid uploaded file.');

        $fixture = new UploadedFile($fileA, null, null, null, rand(), uniqid());
        $fixture->moveTo($fileB);
    }

    public function testMoveToFromStream(): void
    {
        $data   = uniqid();
        $stream = new Stream($data);
        $target = $this->root->url().'/'.uniqid();

        $fixture = new UploadedFile($stream);
        $fixture->moveTo($target);

        $actual = file_get_contents($target);

        self::assertEquals($data, $actual);
    }

    public function testMoveToFromStreamThrowsExceptionWhenNotWritable(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $stream = new Stream(uniqid());
        $path   = $this->root->url().'/'.uniqid();
        $target = $path.'/'.uniqid();
        mkdir($path);
        touch($target);
        chmod($target, 0400);

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Unable to open "'.$target.'" for writing!');

        $fixture = new UploadedFile($stream);
        $fixture->moveTo($target);

        error_reporting($level);
    }

    public function testMoveToFromStreamThrowsExceptionWhenNoResource(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Failed to access uploaded file.');

        $fixture = new UploadedFile($stream);
        $fixture->moveTo($this->root->url().'/'.uniqid());
    }

    public function testMoveToFromStreamThrowsExceptionOnError(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $handle = fopen('php://temp', 'w');
        if (!$handle) {
            self::fail('Failed to open temp stream.');
        }
        $target = $this->root->url().'/'.uniqid();
        $stream = $this->createConfiguredMock(
            StreamInterface::class,
            ['detach' => $handle]
        );
        fclose($handle);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Failed to move file to "'.$target.'".');

        $fixture = new UploadedFile($stream);
        $fixture->moveTo($target);

        error_reporting($level);
    }

    public function testGetSize(): void
    {
        $size = rand();

        $fixture = new UploadedFile(uniqid(), null, null, $size);
        $actual  = $fixture->getSize();

        self::assertEquals($size, $actual);
    }

    public function testGetSizeNull(): void
    {
        $fixture = new UploadedFile(uniqid(), null, null, null);
        $actual  = $fixture->getSize();

        self::assertNull($actual);
    }

    public function testGetSizeDefault(): void
    {
        $fixture = new UploadedFile(uniqid());
        $actual  = $fixture->getSize();

        self::assertNull($actual);
    }

    public function testGetError(): void
    {
        $error = rand();

        $fixture = new UploadedFile(uniqid(), null, null, null, $error);
        $actual  = $fixture->getError();

        self::assertEquals($error, $actual);
    }

    public function testGetErrorDefault(): void
    {
        $fixture = new UploadedFile(uniqid());
        $actual  = $fixture->getError();

        self::assertEquals(UPLOAD_ERR_OK, $actual);
    }

    public function testGetClientFilename(): void
    {
        $name = uniqid();

        $fixture = new UploadedFile(uniqid(), $name);
        $actual  = $fixture->getClientFilename();

        self::assertEquals($name, $actual);
    }

    public function testGetClientFilenameNull(): void
    {
        $fixture = new UploadedFile(uniqid(), null);
        $actual  = $fixture->getClientFilename();

        self::assertNull($actual);
    }

    public function testGetClientFilenameDefault(): void
    {
        $fixture = new UploadedFile(uniqid());
        $actual  = $fixture->getClientFilename();

        self::assertNull($actual);
    }

    public function testGetClientMediaType(): void
    {
        $type = uniqid();

        $fixture = new UploadedFile(uniqid(), null, $type);
        $actual  = $fixture->getClientMediaType();

        self::assertEquals($type, $actual);
    }

    public function testGetClientMediaTypeNull(): void
    {
        $fixture = new UploadedFile(uniqid(), null);
        $actual  = $fixture->getClientMediaType();

        self::assertNull($actual);
    }

    public function testGetClientMediaTypeDefault(): void
    {
        $fixture = new UploadedFile(uniqid());
        $actual  = $fixture->getClientMediaType();

        self::assertNull($actual);
    }
}
