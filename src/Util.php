<?php

namespace Bitty\Http;

class Util
{
    /**
     * Wrapper for fopen.
     *
     * @internal
     *
     * @param string $path
     * @param string $mode
     *
     * @return resource
     *
     * @throws \RuntimeException If unable to open file.
     */
    public static function fopen(string $path, string $mode)
    {
        $fh = fopen($path, $mode);

        if (false === $fh) {
            throw new \RuntimeException(sprintf('Unable to open "%s".', $path));
        }

        return $fh;
    }

    /**
     * Wrapper for fwrite.
     *
     * @internal
     *
     * @param resource $resource
     * @param string $string
     *
     * @return int
     */
    public static function fwrite($resource, string $string): int
    {
        $bytes = fwrite($resource, $string);

        if (false === $bytes) {
            throw new \RuntimeException('Failed to write to stream.');
        }

        return $bytes;
    }

    /**
     * Wrapper for fread.
     *
     * @internal
     *
     * @param resource $resource
     * @param int $length
     *
     * @return string
     */
    public static function fread($resource, int $length): string
    {
        $string = fread($resource, $length);

        if (false === $string) {
            throw new \RuntimeException('Failed to read from stream.');
        }

        return $string;
    }
}
