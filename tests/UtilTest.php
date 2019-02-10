<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testFopen(): void
    {
        $level = error_reporting();
        error_reporting(0);

        $path = uniqid().'/'.uniqid();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Unable to open "'.$path.'".');

        Util::fopen($path, 'r');

        error_reporting($level);
    }

    public function testFwrite(): void
    {
        $level = error_reporting();
        error_reporting(0);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Failed to write to stream.');

        $resource = $this->createInvalidResource();
        Util::fwrite($resource, uniqid());

        error_reporting($level);
    }

    public function testFread(): void
    {
        $level = error_reporting();
        error_reporting(0);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Failed to read from stream.');

        $resource = $this->createInvalidResource();
        Util::fread($resource, rand());

        error_reporting($level);
    }

    /**
     * @return resource
     */
    private function createInvalidResource()
    {
        $resource = fopen('php://temp', 'w');
        if ($resource === false) {
            self::fail('Unable to open temporary resource.');
        }

        fclose($resource);

        return $resource;
    }
}
