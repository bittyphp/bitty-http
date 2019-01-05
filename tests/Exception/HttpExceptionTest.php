<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpException;
use Bitty\Http\Exception\HttpExceptionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new HttpException();

        self::assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage(): void
    {
        $message = uniqid();
        $fixture = new HttpException($message);

        $actual = $fixture->getMessage();

        self::assertEquals($message, $actual);
    }

    public function testCode(): void
    {
        $code    = rand();
        $fixture = new HttpException(null, $code);

        $actual = $fixture->getCode();

        self::assertEquals($code, $actual);
    }

    public function testRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $fixture = new HttpException(null, rand(), $request);

        $actual = $fixture->getRequest();

        self::assertEquals($request, $actual);
    }

    public function testResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $fixture  = new HttpException(null, rand(), null, $response);

        $actual = $fixture->getResponse();

        self::assertEquals($response, $actual);
    }

    public function testTitle(): void
    {
        $fixture = new HttpException();

        $actual = $fixture->getTitle();

        self::assertEquals('', $actual);
    }

    public function testDescription(): void
    {
        $fixture = new HttpException();

        $actual = $fixture->getDescription();

        self::assertEquals('', $actual);
    }
}
