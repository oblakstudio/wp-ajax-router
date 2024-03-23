<?php
namespace XWP\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface Argument {
    /**
     * Get field value
     *
     * @param  ServerRequestInterface $req The request instance.
     * @return mixed
     */
    public function get_value( ServerRequestInterface &$req ): mixed;
}
