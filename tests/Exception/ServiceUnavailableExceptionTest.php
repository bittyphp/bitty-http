<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\ServiceUnavailableException;
use PHPUnit\Framework\TestCase;

class ServiceUnavailableExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new ServiceUnavailableException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getMessage();

        self::assertEquals('Service Unavailable', $actual);
    }

    public function testCode(): void
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getCode();

        self::assertEquals(503, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getTitle();

        self::assertEquals('503 Service Unavailable', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getDescription();

        $description = 'The server is currently unable to handle the request '
            .'due to a temporary overloading or maintenance of the server.';
        self::assertEquals($description, $actual);
    }
}
