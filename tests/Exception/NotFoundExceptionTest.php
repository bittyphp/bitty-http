<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class NotFoundExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new NotFoundException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getMessage();

        self::assertEquals('Not Found', $actual);
    }

    public function testCode(): void
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getCode();

        self::assertEquals(404, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getTitle();

        self::assertEquals('404 Not Found', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getDescription();

        $description = 'The server cannot find the requested resource.';
        self::assertEquals($description, $actual);
    }
}
