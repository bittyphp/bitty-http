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
     * @var StreamInterface|null
     */
    private $stream = null;

    /**
     * Full path to the file.
     *
     * @var string|null
     */
    private $path = null;

    /**
     * Name of the file.
     *
     * @var string|null
     */
    private $name = null;

    /**
     * Media-type of the file.
     *
     * @var string|null
     */
    private $mediaType = null;

    /**
     * Size of the file.
     *
     * @var int|null
     */
    private $size = null;

    /**
     * Error associated to the file.
     *
     * @var int
     */
    private $error = null;

    /**
     * SAPI environment.
     *
     * @var string
     */
    private $sapi = null;

    /**
     * @param StreamInterface|string $streamOrPath
     * @param string|null $name
     * @param string|null $mediaType
     * @param int|null $size
     * @param int $error
     * @param string $sapi
     */
    public function __construct(
        $streamOrPath,
        ?string $name = null,
        ?string $mediaType = null,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $sapi = PHP_SAPI
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
        $this->sapi      = $sapi;
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->path === null) {
            throw new \RuntimeException(
                'Stream not available; the file appears to have been moved.'
            );
        }

        if ($this->stream === null) {
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
    private function verifyMovable(): void
    {
        if ($this->path === null) {
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
    private function verifyTargetPath(string $targetPath): void
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
    private function moveStream(string $targetPath): void
    {
        $fp = fopen($targetPath, 'wb');
        if ($fp === false) {
            throw new \InvalidArgumentException(
                sprintf('Unable to open "%s" for writing!', $targetPath)
            );
        }

        $stream = $this->getStream();
        $stream->rewind();
        $resource = $stream->detach();
        if (!$resource) {
            throw new \RuntimeException('Failed to access uploaded file.');
        }

        if (stream_copy_to_stream($resource, $fp) === false) {
            throw new \RuntimeException(
                sprintf('Failed to move file to "%s".', $targetPath)
            );
        }

        fclose($fp);
    }

    /**
     * Moves the file to the target path.
     *
     * @param string $targetPath
     */
    private function movePath(string $targetPath): void
    {
        $path = $this->path ?: '';

        if ($this->sapi === 'cli') {
            if (!rename($path, $targetPath)) {
                throw new \RuntimeException(
                    sprintf('Failed to move file to "%s".', $targetPath)
                );
            }

            return;
        }

        if (!is_uploaded_file($path)) {
            throw new \RuntimeException('File is not a valid uploaded file.');
        }

        if (!move_uploaded_file($path, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Failed to move file to "%s".', $targetPath)
            );
        }
    }

    /**
     * Finalizes the move.
     */
    private function finalizeMove(): void
    {
        $this->path = null;

        if ($this->stream === null) {
            return;
        }

        $this->stream->close();
        $this->stream = null;
    }
}
