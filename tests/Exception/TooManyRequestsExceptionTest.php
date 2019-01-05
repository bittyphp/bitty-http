<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\TooManyRequestsException;
use PHPUnit\Framework\TestCase;

class TooManyRequestsExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new TooManyRequestsException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getMessage();

        self::assertEquals('Too Many Requests', $actual);
    }

    public function testCode(): void
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getCode();

        self::assertEquals(429, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getTitle();

        self::assertEquals('429 Too Many Requests', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getDescription();

        $description = 'Too many requests sent in a given amount of time.';
        self::assertEquals($description, $actual);
    }
}
