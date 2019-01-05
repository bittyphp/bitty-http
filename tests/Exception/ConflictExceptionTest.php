<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\ConflictException;
use Bitty\Http\Exception\HttpExceptionInterface;
use PHPUnit\Framework\TestCase;

class ConflictExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new ConflictException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new ConflictException();

        $actual = $fixture->getMessage();

        self::assertEquals('Conflict', $actual);
    }

    public function testCode(): void
    {
        $fixture = new ConflictException();

        $actual = $fixture->getCode();

        self::assertEquals(409, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new ConflictException();

        $actual = $fixture->getTitle();

        self::assertEquals('409 Conflict', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new ConflictException();

        $actual = $fixture->getDescription();

        $description = 'The request could not be completed due to a '
            .'conflict with the current state of the resource.';
        self::assertEquals($description, $actual);
    }
}
