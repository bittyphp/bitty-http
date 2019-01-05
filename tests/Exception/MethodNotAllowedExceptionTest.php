<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\MethodNotAllowedException;
use PHPUnit\Framework\TestCase;

class MethodNotAllowedExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new MethodNotAllowedException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getMessage();

        self::assertEquals('Method Not Allowed', $actual);
    }

    public function testCode(): void
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getCode();

        self::assertEquals(405, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getTitle();

        self::assertEquals('405 Method Not Allowed', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getDescription();

        $description = 'The method specified is not allowed for the resource identified.';
        self::assertEquals($description, $actual);
    }
}
