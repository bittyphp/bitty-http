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
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ServerRequest();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->fixture);
    }

    public function testWithQueryParams()
    {
        $params = [uniqid() => uniqid()];

        $clone = $this->fixture->withQueryParams($params);
        $old   = $this->fixture->getQueryParams();
        $new   = $clone->getQueryParams();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([], $old);
        $this->assertEquals($params, $new);
    }

    public function testWithCookieParams()
    {
        $params = [uniqid() => uniqid()];

        $clone = $this->fixture->withCookieParams($params);
        $old   = $this->fixture->getCookieParams();
        $new   = $clone->getCookieParams();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([], $old);
        $this->assertEquals($params, $new);
    }

    public function testWithUploadedFiles()
    {
        $files = [uniqid() => $this->createMock(UploadedFileInterface::class)];

        $clone = $this->fixture->withUploadedFiles($files);
        $old   = $this->fixture->getUploadedFiles();
        $new   = $clone->getUploadedFiles();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([], $old);
        $this->assertEquals($files, $new);
    }

    public function testWithUploadedFilesThrowsException()
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

    public function testGetServerParams()
    {
        $params  = [uniqid() => uniqid()];
        $fixture = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], $params, []);

        $actual = $fixture->getServerParams();

        $this->assertEquals($params, $actual);
    }

    /**
     * @dataProvider samplePostContentTypes
     */
    public function testGetParsedBody($method, $contentType, $request, $body, $expected)
    {
        $headers = ['Content-Type' => $contentType];
        $fixture = new ServerRequest($method, '', $headers, $body, '1.1', [], $request, [], [], [], []);

        $actual = $fixture->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function samplePostContentTypes()
    {
        $params = [uniqid('key') => uniqid('value')];
        $json   = [uniqid('json') => uniqid('value')];

        return [
            'POST application/x-www-form-urlencoded' => [
                'method' => 'POST',
                'contentType' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'request' => $params,
                'body' => '',
                'expected' => $params
            ],
            'multipart/form-data' => [
                'method' => 'POST',
                'contentType' => 'multipart/form-data; charset=UTF-8',
                'request' => $params,
                'body' => '',
                'expected' => $params
            ],
            'non-POST application/x-www-form-urlencoded' => [
                'method' => 'PATCH',
                'contentType' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'request' => $params,
                'body' => 'foo=bar&baz=bur',
                'expected' => ['foo' => 'bar', 'baz' => 'bur']
            ],
            'valid application/json' => [
                'method' => 'POST',
                'contentType' => 'application/json',
                'request' => $params,
                'body' => json_encode($json),
                'expected' => $json
            ],
            'invalid application/json' => [
                'method' => 'POST',
                'contentType' => 'application/json',
                'request' => $params,
                'body' => '[}',
                'expected' => null
            ],
            'unknown' => [
                'method' => 'POST',
                'contentType' => uniqid(),
                'request' => $params,
                'body' => json_encode($json),
                'expected' => null
            ],
        ];
    }

    /**
     * @dataProvider sampleValidParsedBody
     */
    public function testWithParsedBody($parsedBody)
    {
        $clone = $this->fixture->withParsedBody($parsedBody);
        $old   = $this->fixture->getParsedBody();
        $new   = $clone->getParsedBody();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals(null, $old);
        $this->assertEquals($parsedBody, $new);
    }

    public function sampleValidParsedBody()
    {
        return [
            'object' => [new \stdClass()],
            'array' => [[uniqid()]],
            'null' => [null],
        ];
    }

    public function testWithParsedBodyInvalid()
    {
        $message = 'Parsed body must be an array, object, or null; integer given.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withParsedBody(rand());
    }

    public function testGetAttributes()
    {
        $attributes = [uniqid() => uniqid()];
        $fixture    = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], [], $attributes);

        $actual = $fixture->getAttributes();

        $this->assertEquals($attributes, $actual);
    }

    /**
     * @dataProvider sampleAttributes
     */
    public function testGetAttribute($attributes, $name, $default, $expected)
    {
        $fixture = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], [], $attributes);

        $actual = $fixture->getAttribute($name, $default);

        $this->assertEquals($expected, $actual);
    }

    public function sampleAttributes()
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

    public function testWithAttribute()
    {
        $name  = uniqid('name');
        $value = uniqid('value');

        $clone = $this->fixture->withAttribute($name, $value);
        $old   = $this->fixture->getAttribute($name);
        $new   = $clone->getAttribute($name);

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals(null, $old);
        $this->assertEquals($value, $new);
    }

    public function testWithoutAttribute()
    {
        $name       = uniqid('name');
        $value      = uniqid('value');
        $attributes = [$name => $value];
        $fixture    = new ServerRequest('GET', '', [], '', '1.1', [], [], [], [], [], $attributes);

        $clone = $fixture->withoutAttribute($name);
        $old   = $fixture->getAttribute($name);
        $new   = $clone->getAttribute($name);

        $this->assertNotSame($fixture, $clone);
        $this->assertEquals($value, $old);
        $this->assertEquals(null, $new);
    }

    public function testWithoutNonExistentAttribute()
    {
        $name = uniqid('name');

        $clone  = $this->fixture->withoutAttribute($name);
        $actual = $clone->getAttribute($name);

        $this->assertEquals(null, $actual);
    }

    public function testRegisterContentTypeParser()
    {
        $contentType = uniqid('type');
        $callback    = function () {
            return ['decoded'];
        };

        $headers       = ['Content-Type' => $contentType];
        $this->fixture = new ServerRequest('GET', '', $headers);
        $this->fixture->registerContentTypeParser($contentType, $callback);

        $actual = $this->fixture->getParsedBody();

        $this->assertEquals(['decoded'], $actual);
    }
}
