<?php

namespace Bitty\Http;

use Bitty\Http\Stream;

class RequestBody extends Stream
{
    /**
     * Create a wrapper around Stream that automatically grabs the input stream.
     *
     * @param string $source
     * @param string $dest
     */
    public function __construct(string $source = 'php://input', string $dest = 'php://temp')
    {
        $stream = fopen($dest, 'w+');
        $input  = fopen($source, 'r');
        if ($stream && $input) {
            stream_copy_to_stream($input, $stream);
        }

        parent::__construct($stream);
    }
}
