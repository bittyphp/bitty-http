<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\BadGatewayException;
use Bitty\Http\Exception\HttpExceptionInterface;
use PHPUnit\Framework\TestCase;

class BadGatewayExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new BadGatewayException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new BadGatewayException();

        $actual = $fixture->getMessage();

        self::assertEquals('Bad Gateway', $actual);
    }

    public function testCode(): void
    {
        $fixture = new BadGatewayException();

        $actual = $fixture->getCode();

        self::assertEquals(502, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new BadGatewayException();

        $actual = $fixture->getTitle();

        self::assertEquals('502 Bad Gateway', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new BadGatewayException();

        $actual = $fixture->getDescription();

        $description = 'The server received an invalid response from an upstream server.';
        self::assertEquals($description, $actual);
    }
}
