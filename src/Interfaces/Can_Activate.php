<?php
namespace XWP\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface Can_Activate {
    /**
     * Determines if the guard can be activated.
     *
     * @param  ServerRequestInterface $req The request instance.
     * @return bool
     *
     * @throws UnauthorizedException If the guard cannot be activated.
     */
    public function can_activate( ServerRequestInterface $req );
}
