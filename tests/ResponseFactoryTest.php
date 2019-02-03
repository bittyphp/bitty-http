<?php

namespace Bitty\Tests\Http;

use Bitty\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactoryTest extends TestCase
{
    /**
     * @var ResponseFactory
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new ResponseFactory();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(ResponseFactoryInterface::class, $this->fixture);
    }

    public function testCreateResponseDefault(): void
    {
        $actual = $this->fixture->createResponse();

        self::assertInstanceOf(ResponseInterface::class, $actual);
        self::assertEquals(200, $actual->getStatusCode());
        self::assertEquals('OK', $actual->getReasonPhrase());
    }

    /**
     * @param int $code
     * @param string $reason
     * @param string $expected
     *
     * @dataProvider sampleStatus
     */
    public function testCreateResponse(int $code, string $reason, string $expected): void
    {
        $actual = $this->fixture->createResponse($code, $reason);

        self::assertInstanceOf(ResponseInterface::class, $actual);
        self::assertEquals($code, $actual->getStatusCode());
        self::assertEquals($expected, $actual->getReasonPhrase());
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

    /**
     * Gets a list of valid status codes and reasons.
     *
     * @return string[]
     */
    private function getValidStatusCodes()
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
}
