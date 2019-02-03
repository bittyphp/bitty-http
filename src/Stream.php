<?php

namespace Bitty\Http;

use Bitty\Http\Util;
use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * Stream of data.
     *
     * @var resource|null
     */
    private $stream = null;

    /**
     * @param resource|string|mixed $stream
     *
     * @throws \InvalidArgumentException If a resource or string isn't given.
     */
    public function __construct($stream)
    {
        if (is_resource($stream)) {
            $this->stream = $stream;
        } elseif (is_string($stream)) {
            $this->stream = Util::fopen('php://temp', 'w+');
            fwrite($this->stream, $stream);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s must be constructed with a resource or string; %s given.',
                    __CLASS__,
                    gettype($stream)
                )
            );
        }

        rewind($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        if (null === $this->stream) {
            return '';
        }

        $string = stream_get_contents($this->stream, -1, 0);
        if (!$string) {
            return '';
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        if (null === $this->stream) {
            return;
        }

        fclose($this->stream);
        $this->stream = null;
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $stream = $this->stream;

        $this->stream = null;

        return $stream;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        if (null === $this->stream) {
            return null;
        }

        $stats = fstat($this->stream);

        return isset($stats['size']) ? $stats['size'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        if (null === $this->stream) {
            throw new \RuntimeException('Stream is not open.');
        }

        $position = ftell($this->stream);
        if (false === $position) {
            throw new \RuntimeException('Unable to get position of stream.');
        }

        return $position;
    }

    /**
     * {@inheritDoc}
     */
    public function eof(): bool
    {
        return null === $this->stream ? true : feof($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable(): bool
    {
        if (null === $this->stream) {
            return false;
        }

        $seekable = $this->getMetadata('seekable');
        if (null === $seekable) {
            return false;
        }

        return $seekable;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (null === $this->stream) {
            throw new \RuntimeException('Stream is not open.');
        }

        if (0 > fseek($this->stream, $offset, $whence)) {
            throw new \RuntimeException(
                sprintf('Failed to seek to offset %s.', $offset)
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        if (null === $this->stream) {
            throw new \RuntimeException('Stream is not open.');
        }

        if (!rewind($this->stream)) {
            throw new \RuntimeException('Failed to rewind stream.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable(): bool
    {
        if (null === $this->stream) {
            return false;
        }

        $mode = $this->getMetadata('mode');
        if (null === $mode) {
            return false;
        }

        $mode = str_replace(['b', 'e'], '', $mode);

        return in_array($mode, ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'], true);
    }

    /**
     * {@inheritDoc}
     */
    public function write($string): int
    {
        if (null === $this->stream) {
            throw new \RuntimeException('Stream is not open.');
        }

        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }

        return Util::fwrite($this->stream, $string);
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable(): bool
    {
        if (null === $this->stream) {
            return false;
        }

        $mode = $this->getMetadata('mode');
        if (null === $mode) {
            return false;
        }

        $mode = str_replace(['b', 'e'], '', $mode);

        return in_array($mode, ['r', 'r+', 'w+', 'a+', 'x+', 'c+'], true);
    }

    /**
     * {@inheritDoc}
     */
    public function read($length): string
    {
        if (null === $this->stream) {
            throw new \RuntimeException('Stream is not open.');
        }

        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable.');
        }

        return Util::fread($this->stream, $length);
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(): string
    {
        if (null === $this->stream) {
            throw new \RuntimeException('Stream is not open.');
        }

        $string = stream_get_contents($this->stream);
        if (false === $string) {
            throw new \RuntimeException('Failed to get contents of stream.');
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        if (null === $this->stream) {
            return null;
        }

        $metadata = stream_get_meta_data($this->stream);
        if (null === $key) {
            return $metadata;
        }

        if (isset($metadata[$key]) || array_key_exists($key, $metadata)) {
            return $metadata[$key];
        }

        return null;
    }
}
