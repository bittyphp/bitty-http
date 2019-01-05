<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\NotAcceptableException;
use PHPUnit\Framework\TestCase;

class NotAcceptableExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new NotAcceptableException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getMessage();

        self::assertEquals('Not Acceptable', $actual);
    }

    public function testCode(): void
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getCode();

        self::assertEquals(406, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getTitle();

        self::assertEquals('406 Not Acceptable', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getDescription();

        $description = 'The resource is not capable of generating '
            .'responses acceptable to the requested accept headers.';
        self::assertEquals($description, $actual);
    }
}
