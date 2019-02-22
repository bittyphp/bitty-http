<?php

namespace Bitty\Http;

use Bitty\Http\Stream;

class RequestBody extends Stream
{
    /**
     * Create a wrapper around Stream that automatically grabs the input stream.
     */
    public function __construct()
    {
        $stream = fopen('php://temp', 'w+');
        $input  = fopen('php://input', 'r');
        if ($stream !== false && $input !== false) {
            stream_copy_to_stream($input, $stream);
        }

        parent::__construct($stream);
    }
}
