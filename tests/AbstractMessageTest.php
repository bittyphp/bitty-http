<?php

namespace Bitty\Tests\Http;

use Bitty\Http\AbstractMessage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class AbstractMessageTest extends TestCase
{
    /**
     * @var AbstractMessage
     */
    protected $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = $this->getMockForAbstractClass(AbstractMessage::class);
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MessageInterface::class, $this->fixture);
    }

    public function testWithProtocolVersion(): void
    {
        $clone = $this->fixture->withProtocolVersion('1.0');

        $new = $clone->getProtocolVersion();
        $old = $this->fixture->getProtocolVersion();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('1.0', $new);
        self::assertEquals('1.1', $old);
    }

    public function testWithProtocolVersionThrowsException(): void
    {
        $invalidVersion = uniqid();

        $message = 'Invalid protocol version "'.$invalidVersion.'". '
            .'Valid versions are: ["1.0", "1.1", "2.0", "2", "3"]';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->withProtocolVersion($invalidVersion);
    }

    public function testWithHeaderAddsNewHeader(): void
    {
        $header = uniqid('header');
        $value  = uniqid('value');

        $clone = $this->fixture->withHeader($header, $value);

        $new = $clone->getHeaders();
        $old = $this->fixture->getHeaders();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals([$header => [$value]], $new);
        self::assertEquals([], $old);
    }

    public function testWithHeaderReplacesExistingHeader(): void
    {
        $headerA = uniqid('header');
        $headerB = strtoupper($headerA);
        $headerC = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $valueC  = uniqid('value');

        $cloneA = $this->fixture->withHeader($headerA, $valueA)->withHeader($headerC, $valueC);
        $old    = $cloneA->getHeaders();

        $cloneB = $cloneA->withHeader($headerB, $valueB);
        $new    = $cloneB->getHeaders();

        self::assertNotSame($cloneA, $cloneB);
        self::assertEquals([$headerC => [$valueC], $headerA => [$valueA]], $old);
        self::assertEquals([$headerC => [$valueC], $headerB => [$valueB]], $new);
    }

    /**
     * @param string $header
     * @param mixed $value
     * @param string $expected
     *
     * @dataProvider sampleHeaderExceptions
     */
    public function testWithHeaderThrowsException(
        string $header,
        $value,
        string $expected
    ): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        $this->fixture->withHeader($header, $value);
    }

    public function testWithAddedHeaderAddsNewHeader(): void
    {
        $header = uniqid('header');
        $value  = uniqid('value');

        $clone = $this->fixture->withAddedHeader($header, $value);

        $new = $clone->getHeaders();
        $old = $this->fixture->getHeaders();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals([$header => [$value]], $new);
        self::assertEquals([], $old);
    }

    public function testWithAddedHeaderAddsToExistingHeader(): void
    {
        $headerA = uniqid('header');
        $headerB = strtoupper($headerA);
        $headerC = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $valueC  = uniqid('value');

        $cloneA = $this->fixture->withHeader($headerA, $valueA)->withHeader($headerC, $valueC);
        $old    = $cloneA->getHeaders();

        $cloneB = $cloneA->withAddedHeader($headerB, $valueB);
        $new    = $cloneB->getHeaders();

        self::assertNotSame($cloneA, $cloneB);
        self::assertEquals([$headerA => [$valueA], $headerC => [$valueC]], $old);
        self::assertEquals([$headerA => [$valueA, $valueB], $headerC => [$valueC]], $new);
    }

    /**
     * @param string $header
     * @param mixed $value
     * @param string $expected
     *
     * @dataProvider sampleHeaderExceptions
     */
    public function testWithAddedHeaderThrowsException(
        string $header,
        $value,
        string $expected
    ): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        $this->fixture->withAddedHeader($header, $value);
    }

    public function sampleHeaderExceptions(): array
    {
        $header = uniqid('header');

        return [
            'object value type' => [
                $header,
                new \stdClass(),
                'Values for header "'.$header.'" must be a string or array; object given.',
            ],
            'null value type' => [
                $header,
                null,
                'Values for header "'.$header.'" must be a string or array; NULL given.',
            ],
            'false value type' => [
                $header,
                (bool) rand(0, 1),
                'Values for header "'.$header.'" must be a string or array; boolean given.',
            ],
            'int value type' => [
                $header,
                rand(),
                'Values for header "'.$header.'" must be a string or array; integer given.',
            ],
            'object array value' => [
                $header,
                [uniqid(), new \stdClass(), uniqid()],
                'Values for header "'.$header.'" must contain only strings; object given.',
            ],
            'array array value' => [
                $header,
                [uniqid(), [], uniqid()],
                'Values for header "'.$header.'" must contain only strings; array given.',
            ],
            'null value type in array' => [
                $header,
                [uniqid(), null, uniqid()],
                'Values for header "'.$header.'" must contain only strings; NULL given.',
            ],
            'false value type in array' => [
                $header,
                [uniqid(), (bool) rand(0, 1), uniqid()],
                'Values for header "'.$header.'" must contain only strings; boolean given.',
            ],
            'int value type in array' => [
                $header,
                [uniqid(), rand(), uniqid()],
                'Values for header "'.$header.'" must contain only strings; integer given.',
            ],
        ];
    }

    public function testInvalidHeaderThrowsException(): void
    {
        $invalid = [rand(1, 32), 34, 40, 41, 44, 47, rand(58, 64), 91, 92, 93, 123, rand(125, 255)];
        $header  = uniqid().chr($invalid[array_rand($invalid)]).uniqid();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header "'.$header.'" contains invalid characters.');

        $this->fixture->withAddedHeader($header, uniqid());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidHeaderDoesNotThrowException(): void
    {
        $header = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!#$%&\'*+-.^_`|';

        $this->fixture->withAddedHeader($header, uniqid());
    }

    public function testInvalidHeaderValueThrowsException(): void
    {
        $invalid = [rand(1, 8), rand(10, 19), 127];
        $value   = uniqid().chr($invalid[array_rand($invalid)]).uniqid();
        $header  = uniqid('header');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header "'.$header.'" contains invalid value "'.$value.'".');

        $this->fixture->withAddedHeader($header, $value);
    }

    public function testWithoutHeaderWhenHeaderNotPresent(): void
    {
        $clone = $this->fixture->withoutHeader(uniqid());

        $new = $clone->getHeaders();
        $old = $this->fixture->getHeaders();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals([], $new);
        self::assertEquals([], $old);
    }

    public function testWithoutHeaderWhenHeaderIsPresent(): void
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');

        $cloneA = $this->fixture->withHeader($headerA, $valueA)->withHeader($headerB, $valueB);
        $old    = $cloneA->getHeaders();

        $cloneB = $cloneA->withoutHeader(strtoupper($headerA));
        $new    = $cloneB->getHeaders();

        self::assertNotSame($cloneA, $cloneB);
        self::assertEquals([$headerA => [$valueA], $headerB => [$valueB]], $old);
        self::assertEquals([$headerB => [$valueB]], $new);
    }

    public function testHasHeaderWhenNotPresent(): void
    {
        $actual = $this->fixture->hasHeader(uniqid());

        self::assertFalse($actual);
    }

    public function testHasHeaderWhenPresent(): void
    {
        $header = uniqid('header');

        $clone  = $this->fixture->withHeader($header, uniqid());
        $actual = $clone->hasHeader(strtoupper($header));

        self::assertTrue($actual);
    }

    public function testGetHeaderWhenNotPresent(): void
    {
        $actual = $this->fixture->getHeader(uniqid());

        self::assertEquals([], $actual);
    }

    public function testGetHeaderWhenPresent(): void
    {
        $header = uniqid('header');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        $clone  = $this->fixture->withHeader($header, [$valueA, $valueB]);
        $actual = $clone->getHeader(strtoupper($header));

        self::assertEquals([$valueA, $valueB], $actual);
    }

    public function testGetHeaderLineWhenNotPresent(): void
    {
        $actual = $this->fixture->getHeaderLine(uniqid());

        self::assertEquals('', $actual);
    }

    public function testGetHeaderLineWhenPresent(): void
    {
        $header = uniqid('header');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        $clone  = $this->fixture->withHeader($header, [$valueA, $valueB]);
        $actual = $clone->getHeaderLine(strtoupper($header));

        self::assertEquals($valueA.','.$valueB, $actual);
    }

    public function testWithBody(): void
    {
        $body = $this->createConfiguredMock(StreamInterface::class, ['getContents' => uniqid()]);

        $clone = $this->fixture->withBody($body);
        $old   = $this->fixture->getBody();
        $new   = $clone->getBody();

        self::assertNotSame($this->fixture, $clone);
        self::assertEquals('', $old->getContents());
        self::assertNotSame($body, $new);
        self::assertSame($body->getContents(), $new->getContents());
    }
}
