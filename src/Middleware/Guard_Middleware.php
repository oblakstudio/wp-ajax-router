<?php
namespace XWP\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use XWP\Decorator\Core\Use_Guards;
use XWP\Interfaces\Can_Activate;

class Guard_Middleware implements MiddlewareInterface {
    public const DECORATOR_CLASS = Use_Guards::class;

    /**
     * The guards that will be used to protect the route.
     *
     * @var array<Can_Activate>
     */
    protected array $guards = array();

    public function __construct( Use_Guards ...$use_guards ) {
        $this->guards = \array_merge( ...\wp_list_pluck( $use_guards, 'guards' ) );
    }

    public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
        \array_walk( $this->guards, static fn( $g ) => $g->can_activate( $request ) );

        return $handler->handle( $request );
    }
}
