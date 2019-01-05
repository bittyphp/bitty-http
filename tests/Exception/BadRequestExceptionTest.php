<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\BadRequestException;
use Bitty\Http\Exception\HttpExceptionInterface;
use PHPUnit\Framework\TestCase;

class BadRequestExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new BadRequestException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getMessage();

        self::assertEquals('Bad Request', $actual);
    }

    public function testCode(): void
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getCode();

        self::assertEquals(400, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getTitle();

        self::assertEquals('400 Bad Request', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getDescription();

        $description = 'The request could not be understood by the server '
            .'due to malformed syntax.';
        self::assertEquals($description, $actual);
    }
}
