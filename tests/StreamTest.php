<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class StreamTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new Stream('');

        self::assertInstanceOf(StreamInterface::class, $fixture);
    }

    public function testExceptionThrown(): void
    {
        $message = Stream::class.' must be constructed with a resource or string; integer given.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new Stream(rand());
    }

    public function testToString(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        self::assertEquals($content, (string) $fixture);
    }

    public function testToStringWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        self::assertEquals('', (string) $fixture);
    }

    public function testCloseWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());

        $fixture->detach();

        try {
            $fixture->close();
        } catch (\Throwable $e) {
            self::fail($e->getMessage());
        }

        self::assertTrue(true);
    }

    public function testClose(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);

        $fixture->close();

        fwrite($resource, uniqid());
        rewind($resource);

        self::assertEquals('', stream_get_contents($resource));

        error_reporting($level);
    }

    public function testDetach(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $stream = $fixture->detach();
        $actual = null;
        if ($stream) {
            $actual = stream_get_contents($stream, -1, 0);
        }

        self::assertEquals('', (string) $fixture);
        self::assertEquals($content, $actual);
    }

    public function testGetSize(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $actual = $fixture->getSize();

        self::assertEquals(strlen($content), $actual);
    }

    public function testGetSizeWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $actual = $fixture->getSize();

        self::assertNull($actual);
    }

    public function testTell(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        self::assertEquals(0, $fixture->tell());
        $fixture->seek(2);
        self::assertEquals(2, $fixture->tell());
    }

    public function testTellWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $message = 'Stream is not open.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->tell();
    }

    public function testTellFailure(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        $message = 'Unable to get position of stream.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->tell();

        error_reporting($level);
    }

    public function testEof(): void
    {
        $fixture = new Stream(uniqid());

        self::assertFalse($fixture->eof());
        $fixture->seek(0, SEEK_END);
        $fixture->read(1);
        self::assertTrue($fixture->eof());
    }

    public function testEofWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        self::assertTrue($fixture->eof());
    }

    public function testIsSeekable(): void
    {
        $fixture = new Stream(uniqid());

        self::assertTrue($fixture->isSeekable());
    }

    public function testIsSeekableWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        self::assertFalse($fixture->isSeekable());
    }

    public function testIsSeekableNullMetadata(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        self::assertFalse($fixture->isSeekable());

        error_reporting($level);
    }

    public function testSeek(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $seek = rand(0, strlen($content));
        try {
            $fixture->seek($seek, SEEK_SET);
        } catch (\Throwable $e) {
            self::fail($e->getMessage());
        }

        self::assertTrue(true);
    }

    public function testSeekThrowsException(): void
    {
        $fixture = new Stream(uniqid());

        $message = 'Failed to seek to offset 1.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->seek(1, SEEK_END);
    }

    public function testSeekWhenNotSeekable(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $message = 'Stream is not open.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->seek(rand());
    }

    public function testRewind(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $fixture->seek(0, SEEK_END);
        self::assertEquals(strlen($content), $fixture->tell());

        $fixture->rewind();
        self::assertEquals(0, $fixture->tell());
    }

    public function testRewindWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $message = 'Stream is not open.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->rewind();
    }

    public function testRewindFailure(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        $message = 'Failed to rewind stream.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->rewind();

        error_reporting($level);
    }

    public function testIsWritable(): void
    {
        $fixture = new Stream(uniqid());

        self::assertTrue($fixture->isWritable());
    }

    public function testIsWritableWhenReadOnly(): void
    {
        $stream  = fopen('php://temp', 'r');
        $fixture = new Stream($stream);

        self::assertFalse($fixture->isWritable());
    }

    public function testIsWritableWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        self::assertFalse($fixture->isWritable());
    }

    public function testIsWritableNullMetadata(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        self::assertFalse($fixture->isWritable());

        error_reporting($level);
    }

    /**
     * @param int $bytes
     * @param string $content
     *
     * @dataProvider sampleWriteData
     */
    public function testWrite(int $bytes, string $content): void
    {
        $fixture = new Stream('');

        $actual = $fixture->write($content);

        self::assertEquals($bytes, $actual);
        self::assertEquals($content, (string) $fixture);
    }

    public function sampleWriteData(): array
    {
        $data = uniqid();

        return [
            'empty string' => [
                'bytes' => 0,
                'content' => '',
            ],
            'non-empty string' => [
                'bytes' => strlen($data),
                'content' => $data,
            ],
        ];
    }

    public function testWriteFromStream(): void
    {
        $handle = $this->createResource();
        fwrite($handle, uniqid());

        $content = uniqid('data');
        $fixture = new Stream($handle);

        $fixture->write($content);

        self::assertEquals($content, (string) $fixture);
    }

    public function testWriteWhenNotAttached(): void
    {
        $fixture = new Stream('');
        $fixture->close();

        $message = 'Stream is not open.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->write(uniqid());
    }

    public function testWriteWhenNotWritable(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        $message = 'Stream is not writable.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->write(uniqid());

        error_reporting($level);
    }

    public function testIsReadable(): void
    {
        $fixture = new Stream(uniqid());

        self::assertTrue($fixture->isReadable());
    }

    public function testIsReadableWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        self::assertFalse($fixture->isReadable());
    }

    public function testIsReadableNullMetadata(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        self::assertFalse($fixture->isReadable());

        error_reporting($level);
    }

    public function testRead(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $actual = $fixture->read(strlen($content));

        self::assertEquals($content, $actual);
    }

    public function testReadWhenNotAttached(): void
    {
        $fixture = new Stream('');
        $fixture->close();

        $message = 'Stream is not open.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->read(rand());
    }

    public function testReadWhenNotReadable(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        $message = 'Stream is not readable.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->read(rand());

        error_reporting($level);
    }

    public function testGetContents(): void
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $actual = $fixture->getContents();

        self::assertEquals($content, $actual);
    }

    public function testGetContentsWhenNotAttached(): void
    {
        $fixture = new Stream('');
        $fixture->close();

        $message = 'Stream is not open.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->getContents();
    }

    public function testGetContentsFailure(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $resource = $this->createResource();
        $fixture  = new Stream($resource);
        fclose($resource);

        $message = 'Failed to get contents of stream.';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        $fixture->getContents();

        error_reporting($level);
    }

    public function testGetMetadata(): void
    {
        $fixture = new Stream(uniqid());

        $actual = $fixture->getMetadata();

        $expected = [
            'wrapper_type' => 'PHP',
            'stream_type' => 'TEMP',
            'mode' => 'w+b',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => 'php://temp',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testGetMetadataWithKey(): void
    {
        $fixture = new Stream(uniqid());

        $actual = $fixture->getMetadata('uri');

        self::assertEquals('php://temp', $actual);
    }

    public function testGetMetadataWithUnknownKey(): void
    {
        $fixture = new Stream(uniqid());

        $actual = $fixture->getMetadata(uniqid());

        self::assertNull($actual);
    }

    public function testGetMetadataWhenNotAttached(): void
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $actual = $fixture->getMetadata();

        self::assertNull($actual);
    }

    /**
     * @return resource
     */
    private function createResource()
    {
        $resource = fopen('php://temp', 'w');
        if ($resource === false) {
            self::fail('Unable to open temporary resource.');
        }

        return $resource;
    }
}
