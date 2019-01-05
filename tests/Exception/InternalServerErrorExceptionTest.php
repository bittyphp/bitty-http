<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\InternalServerErrorException;
use PHPUnit\Framework\TestCase;

class InternalServerErrorExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new InternalServerErrorException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getMessage();

        self::assertEquals('Internal Server Error', $actual);
    }

    public function testCode(): void
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getCode();

        self::assertEquals(500, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getTitle();

        self::assertEquals('500 Internal Server Error', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getDescription();

        $description = 'The server encountered an unexpected condition '
            .'which prevented it from fulfilling the request.';
        self::assertEquals($description, $actual);
    }
}
