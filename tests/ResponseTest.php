<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    protected $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Response();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(ResponseInterface::class, $this->fixture);
    }

    /**
     * @param int $code
     * @param string $reason
     * @param string $expected
     *
     * @dataProvider sampleStatus
     */
    public function testWithStatusCodeOnly(int $code, string $reason, string $expected): void
    {
        $clone = $this->fixture->withStatus($code, $reason);

        $oldCode   = $this->fixture->getStatusCode();
        $oldReason = $this->fixture->getReasonPhrase();
        $newCode   = $clone->getStatusCode();
        $newReason = $clone->getReasonPhrase();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals(200, $oldCode);
        self::assertEquals('OK', $oldReason);
        self::assertEquals($code, $newCode);
        self::assertEquals($expected, $newReason);
    }

    public function sampleStatus(): array
    {
        $validStatusCodes = $this->getValidStatusCodes();

        $data = [];
        foreach ($validStatusCodes as $code => $reason) {
            $data['default '.$code] = [
                'code' => $code,
                'reason' => '',
                'expected' => $reason,
            ];
        }

        $data['reason override'] = [
            'code' => 302,
            'reason' => 'Somewhat Found',
            'expected' => 'Somewhat Found',
        ];

        return $data;
    }

    public function testWithStatusThrowsException(): void
    {
        $code = $this->getInvalidStatusCode();

        $message = 'Unknown HTTP status code "'.$code.'"';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withStatus($code);
    }

    public function testBody(): void
    {
        $body = uniqid();

        $fixture = new Response($body);

        self::assertEquals($body, (string) $fixture->getBody());
    }

    public function testStatus(): void
    {
        $codes  = $this->getValidStatusCodes();
        $code   = intval(array_rand($codes));
        $reason = $codes[$code];

        $fixture = new Response('', $code);

        self::assertEquals($code, $fixture->getStatusCode());
        self::assertEquals($reason, $fixture->getReasonPhrase());
    }

    public function testInvalidStatusCodeThrowsException(): void
    {
        $code = $this->getInvalidStatusCode();

        $message = 'Unknown HTTP status code "'.$code.'"';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new Response('', $code);
    }

    public function testHeaders(): void
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');

        $fixture = new Response('', 200, [$headerA => $valueA, $headerB => [$valueB]]);

        $actual   = $fixture->getHeaders();
        $expected = [$headerA => [$valueA], $headerB => [$valueB]];

        self::assertEquals($expected, $actual);
    }

    /**
     * Gets a list of valid status codes and reasons.
     *
     * @return string[]
     */
    protected function getValidStatusCodes(): array
    {
        return [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            103 => 'Early Hints',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];
    }

    /**
     * Gets an invalid status code.
     *
     * @return int
     */
    protected function getInvalidStatusCode()
    {
        $validStatusCodes = $this->getValidStatusCodes();

        do {
            $code = rand(1, 999);
        } while (isset($validStatusCodes[$code]));

        return $code;
    }
}
