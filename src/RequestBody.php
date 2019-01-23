<?php

namespace Bitty\Http;

use Bitty\Http\Stream;
use Bitty\Http\Util;

class RequestBody extends Stream
{
    /**
     * Create a wrapper around Stream that automatically grabs the input stream.
     */
    public function __construct()
    {
        $stream = Util::fopen('php://temp', 'w+');
        $input  = Util::fopen('php://input', 'r');
        stream_copy_to_stream($input, $stream);

        parent::__construct($stream);
    }
}
