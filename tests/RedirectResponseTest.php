<?php

namespace Bitty\Tests\Http;

use Bitty\Http\RedirectResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RedirectResponseTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $fixture = new RedirectResponse(uniqid());

        self::assertInstanceOf(ResponseInterface::class, $fixture);
    }

    public function testDefaults(): void
    {
        $uri = uniqid();

        $fixture = new RedirectResponse($uri);

        $expected = '<html><body><p>This page has been moved <a href="'
            .htmlspecialchars($uri, ENT_QUOTES, 'UTF-8')
            .'">here</a>.</p></body></html>';
        self::assertEquals($expected, (string) $fixture->getBody());
        self::assertEquals(302, $fixture->getStatusCode());
        self::assertEquals(['Location' => [$uri]], $fixture->getHeaders());
    }

    public function testHeaders(): void
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $uri     = uniqid();

        $fixture = new RedirectResponse(
            $uri,
            200,
            [
                $headerA => $valueA,
                'LoCaTioN' => uniqid(),
                $headerB => [$valueB],
            ]
        );

        $actual   = $fixture->getHeaders();
        $expected = [
            $headerA => [$valueA],
            'Location' => [$uri],
            $headerB => [$valueB],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testStatusCode(): void
    {
        $statusCode = rand(1, 5) * 100 + rand(0, 3);

        $fixture = new RedirectResponse(uniqid(), $statusCode);
        $actual  = $fixture->getStatusCode();

        self::assertEquals($statusCode, $actual);
    }
}
