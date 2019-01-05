<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\UnauthorizedException;
use PHPUnit\Framework\TestCase;

class UnauthorizedExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new UnauthorizedException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getMessage();

        self::assertEquals('Unauthorized', $actual);
    }

    public function testCode(): void
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getCode();

        self::assertEquals(401, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getTitle();

        self::assertEquals('401 Unauthorized', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getDescription();

        $description = 'The request requires user authentication.';
        self::assertEquals($description, $actual);
    }
}
