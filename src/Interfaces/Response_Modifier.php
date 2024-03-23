<?php
namespace XWP\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface Response_Modifier {
    public function modify( ResponseInterface $res ): ResponseInterface;
}
