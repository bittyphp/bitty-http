<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Headers;
use PHPUnit\Framework\TestCase;

class HeadersTests extends TestCase
{
    public function testGetHeaders()
    {
        $server   = [
            'HTTP_HOST' => uniqid(),
            'HTTP_FOO_BAR' => uniqid(),
            'CONTENT_TYPE' => uniqid(),
            'CONTENT_MD5' => uniqid(),
            'CONTENT_LENGTH' => rand(),
        ];
        $expected = [
            'Host' => [$server['HTTP_HOST']],
            'Foo-Bar' => [$server['HTTP_FOO_BAR']],
            'Content-Type' => [$server['CONTENT_TYPE']],
            'Content-MD5' => [$server['CONTENT_MD5']],
            'Content-Length' => [$server['CONTENT_LENGTH']],
        ];

        $fixture = new Headers();
        $actual  = $fixture->getHeaders($server);

        $this->assertEquals($expected, $actual);
    }
}
