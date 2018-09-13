<?php

namespace Bitty\Http;

use Bitty\Http\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * File contents stream.
     *
     * @var StreamInterface
     */
    protected $stream = null;

    /**
     * Full path to the file.
     *
     * @var string
     */
    protected $path = null;

    /**
     * Name of the file.
     *
     * @var string|null
     */
    protected $name = null;

    /**
     * Media-type of the file.
     *
     * @var string|null
     */
    protected $mediaType = null;

    /**
     * Size of the file.
     *
     * @var int|null
     */
    protected $size = null;

    /**
     * Error associated to the file.
     *
     * @var int
     */
    protected $error = null;

    /**
     * @param StreamInterface|string $streamOrPath
     * @param string|null $name
     * @param string|null $mediaType
     * @param int|null $size
     * @param int $error
     */
    public function __construct(
        $streamOrPath,
        $name = null,
        $mediaType = null,
        $size = null,
        $error = UPLOAD_ERR_OK
    ) {
        if ($streamOrPath instanceof StreamInterface) {
            $this->stream = $streamOrPath;
            $this->path   = '';
        } else {
            $this->path = $streamOrPath;
        }

        $this->name      = $name;
        $this->mediaType = $mediaType;
        $this->size      = $size;
        $this->error     = $error;
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): StreamInterface
    {
        if (null === $this->path) {
            throw new \RuntimeException(
                'Stream not available; the file appears to have been moved.'
            );
        }

        if (null === $this->stream) {
            // lazy load, since we don't always need it
            $this->stream = new Stream(fopen($this->path, 'r'));
        }

        return $this->stream;
    }

    /**
     * {@inheritDoc}
     */
    public function moveTo($targetPath): void
    {
        $this->verifyMovable();
        $this->verifyTargetPath($targetPath);

        if (empty($this->path)) {
            $this->moveStream($targetPath);
        } else {
            $this->movePath($targetPath);
        }

        $this->finalizeMove();
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->mediaType;
    }

    /**
     * Verifies a move hasn't already happened.
     */
    protected function verifyMovable(): void
    {
        if (null === $this->path) {
            throw new \RuntimeException(
                'Unable to perform move; the file has already been moved.'
            );
        }
    }

    /**
     * Verifies a target path is valid.
     *
     * @param string $targetPath
     */
    protected function verifyTargetPath(string $targetPath): void
    {
        if (!is_writable(dirname($targetPath))) {
            throw new \InvalidArgumentException(
                sprintf('Target path "%s" is not writable!', $targetPath)
            );
        }
    }

    /**
     * Moves the stream to the target path.
     *
     * @param string $targetPath
     */
    protected function moveStream(string $targetPath): void
    {
        if (false === ($fp = fopen($targetPath, 'wb'))) {
            throw new \InvalidArgumentException(
                sprintf('Unable to open "%s" for writing!', $targetPath)
            );
        }

        $stream = $this->getStream();
        $stream->rewind();

        if (false === stream_copy_to_stream($stream, $fp, -1, 0)) {
            throw new \RuntimeException(
                sprintf('Failed to move file to "%s"', $targetPath)
            );
        }

        fclose($fp);
    }

    /**
     * Moves the file to the target path.
     *
     * @param string $targetPath
     */
    protected function movePath(string $targetPath): void
    {
        if ('cli' === PHP_SAPI) {
            if (!rename($this->path, $targetPath)) {
                throw new \RuntimeException(
                    sprintf('Failed to move file to "%s"', $targetPath)
                );
            }

            return;
        }

        if (!is_uploaded_file($this->path)) {
            throw new \RuntimeException('File is not a valid uploaded file.');
        }

        if (!move_uploaded_file($this->path, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Failed to move file to "%s"', $targetPath)
            );
        }
    }

    /**
     * Finalizes the move.
     */
    protected function finalizeMove(): void
    {
        $this->path = null;

        if (null !== $this->stream) {
            $this->stream->close();
            $this->stream = null;
        }
    }
}
