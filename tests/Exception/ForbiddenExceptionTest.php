<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\ForbiddenException;
use Bitty\Http\Exception\HttpExceptionInterface;
use PHPUnit\Framework\TestCase;

class ForbiddenExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new ForbiddenException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getMessage();

        self::assertEquals('Forbidden', $actual);
    }

    public function testCode(): void
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getCode();

        self::assertEquals(403, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getTitle();

        self::assertEquals('403 Forbidden', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getDescription();

        $description = 'The server understood the request, but is refusing to fulfill it.';
        self::assertEquals($description, $actual);
    }
}
