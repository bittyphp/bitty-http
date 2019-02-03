<?php

namespace Bitty\Tests\Http;

use Bitty\Http\RequestBody;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class RequestBodyTest extends TestCase
{
    /**
     * @var RequestBody
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new RequestBody();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(StreamInterface::class, $this->fixture);
    }
}
