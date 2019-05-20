<?php

namespace Bitty\Tests\Http;

use Bitty\Http\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class ServerRequestTest extends TestCase
{
    /**
     * @var ServerRequest
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new ServerRequest();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(ServerRequestInterface::class, $this->fixture);
    }

    public function testWithQueryParams(): void
    {
        $params = [uniqid() => uniqid()];

        $clone = $this->fixture->withQueryParams($params);
        $old   = $this->fixture->getQueryParams();
        $new   = $clone->getQueryParams();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals([], $old);
        self::assertEquals($params, $new);
    }

    public function testWithCookieParams(): void
    {
        $params = [uniqid() => uniqid()];

        $clone = $this->fixture->withCookieParams($params);
        $old   = $this->fixture->getCookieParams();
        $new   = $clone->getCookieParams();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals([], $old);
        self::assertEquals($params, $new);
    }

    public function testWithUploadedFiles(): void
    {
        $files = [uniqid() => $this->createMock(UploadedFileInterface::class)];

        $clone = $this->fixture->withUploadedFiles($files);
        $old   = $this->fixture->getUploadedFiles();
        $new   = $clone->getUploadedFiles();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals([], $old);
        self::assertEquals($files, $new);
    }

    public function testWithUploadedFilesThrowsException(): void
    {
        $files = [
            uniqid() => [
                uniqid() => rand(),
            ],
        ];

        $message = 'Files can only contain instances of '.UploadedFileInterface::class;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withUploadedFiles($files);
    }

    public function testGetServerParams(): void
    {
        $params  = [uniqid() => uniqid()];
        $fixture = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], $params, []);

        $actual = $fixture->getServerParams();

        self::assertEquals($params, $actual);
    }

    public function testWithServerParams(): void
    {
        $params = [uniqid() => uniqid()];

        $clone = $this->fixture->withServerParams($params);
        $old   = $this->fixture->getServerParams();
        $new   = $clone->getServerParams();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals([], $old);
        self::assertEquals($params, $new);
    }

    /**
     * @param string $method
     * @param string $contentType
     * @param array $request
     * @param string $body
     * @param array|null $expected
     *
     * @dataProvider samplePostContentTypes
     */
    public function testGetParsedBody(
        string $method,
        string $contentType,
        array $request,
        string $body,
        ?array $expected
    ): void {
        $headers = ['Content-Type' => $contentType];
        $fixture = new ServerRequest($method, '', $headers, $body, '1.1', [], $request, [], [], [], []);

        $actual = $fixture->getParsedBody();

        self::assertEquals($expected, $actual);
    }

    public function samplePostContentTypes(): array
    {
        $params = [uniqid('key') => uniqid('value')];
        $json   = [uniqid('json') => uniqid('value')];

        return [
            'POST application/x-www-form-urlencoded' => [
                'method' => 'POST',
                'contentType' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'request' => $params,
                'body' => '',
                'expected' => $params,
            ],
            'multipart/form-data' => [
                'method' => 'POST',
                'contentType' => 'multipart/form-data; charset=UTF-8',
                'request' => $params,
                'body' => '',
                'expected' => $params,
            ],
            'non-POST application/x-www-form-urlencoded' => [
                'method' => 'PATCH',
                'contentType' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'request' => $params,
                'body' => 'foo=bar&baz=bur',
                'expected' => ['foo' => 'bar', 'baz' => 'bur'],
            ],
            'valid application/json' => [
                'method' => 'POST',
                'contentType' => 'application/json',
                'request' => $params,
                'body' => json_encode($json),
                'expected' => $json,
            ],
            'invalid application/json' => [
                'method' => 'POST',
                'contentType' => 'application/json',
                'request' => $params,
                'body' => '[}',
                'expected' => null,
            ],
            'unknown' => [
                'method' => 'POST',
                'contentType' => uniqid(),
                'request' => $params,
                'body' => json_encode($json),
                'expected' => null,
            ],
        ];
    }

    /**
     * @param mixed $parsedBody
     *
     * @dataProvider sampleValidParsedBody
     */
    public function testWithParsedBody($parsedBody): void
    {
        $clone = $this->fixture->withParsedBody($parsedBody);
        $old   = $this->fixture->getParsedBody();
        $new   = $clone->getParsedBody();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals(null, $old);
        self::assertEquals($parsedBody, $new);
    }

    public function sampleValidParsedBody(): array
    {
        return [
            'object' => [new \stdClass()],
            'array' => [[uniqid()]],
            'null' => [null],
        ];
    }

    public function testWithParsedBodyInvalid(): void
    {
        $message = 'Parsed body must be an array, object, or null; integer given.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withParsedBody(rand());
    }

    public function testGetAttributes(): void
    {
        $attributes = [uniqid() => uniqid()];
        $fixture    = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], [], $attributes);

        $actual = $fixture->getAttributes();

        self::assertEquals($attributes, $actual);
    }

    /**
     * @param array $attributes
     * @param string $name
     * @param string $default
     * @param string $expected
     *
     * @dataProvider sampleAttributes
     */
    public function testGetAttribute(
        array $attributes,
        string $name,
        string $default,
        string $expected
    ): void {
        $fixture = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], [], $attributes);

        $actual = $fixture->getAttribute($name, $default);

        self::assertEquals($expected, $actual);
    }

    public function sampleAttributes(): array
    {
        $nameA  = uniqid('name');
        $nameB  = uniqid('name');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        return [
            'existing attribute' => [
                'attributes' => [$nameA => $valueA, $nameB => $valueB],
                'name' => $nameB,
                'default' => uniqid(),
                'expected' => $valueB,
            ],
            'non-existing attribute' => [
                'attributes' => [$nameA => $valueA],
                'name' => uniqid(),
                'default' => $valueB,
                'expected' => $valueB,
            ],
        ];
    }

    public function testWithAttribute(): void
    {
        $name  = uniqid('name');
        $value = uniqid('value');

        $clone = $this->fixture->withAttribute($name, $value);
        $old   = $this->fixture->getAttribute($name);
        $new   = $clone->getAttribute($name);

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals(null, $old);
        self::assertEquals($value, $new);
    }

    public function testWithoutAttribute(): void
    {
        $name       = uniqid('name');
        $value      = uniqid('value');
        $attributes = [$name => $value];
        $fixture    = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], [], $attributes);

        $clone = $fixture->withoutAttribute($name);
        $old   = $fixture->getAttribute($name);
        $new   = $clone->getAttribute($name);

        self::assertNotSame($fixture, $clone);
        self::assertEquals($value, $old);
        self::assertEquals(null, $new);
    }

    public function testWithoutNonExistentAttribute(): void
    {
        $name = uniqid('name');

        $clone  = $this->fixture->withoutAttribute($name);
        $actual = $clone->getAttribute($name);

        self::assertEquals(null, $actual);
    }

    public function testRegisterContentTypeParser(): void
    {
        $contentType = uniqid('type');
        $callback    = function () {
            return ['decoded'];
        };

        $headers       = ['Content-Type' => $contentType];
        $this->fixture = new ServerRequest('GET', '', $headers);
        $this->fixture->registerContentTypeParser($contentType, $callback);

        $actual = $this->fixture->getParsedBody();

        self::assertEquals(['decoded'], $actual);
    }

    public function testCreateFromGlobals(): void
    {
        $actual = ServerRequest::createFromGlobals();

        self::assertInstanceOf(ServerRequestInterface::class, $actual);
    }
}
