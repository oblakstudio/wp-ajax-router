<?php
namespace XWP\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

interface Response_Type {
    public function create_stream( mixed $data, ResponseInterface &$res ): StreamInterface;
}
