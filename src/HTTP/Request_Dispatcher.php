<?php
/**
 * Request_Dispatcher class file.
 *
 * @package eXtended WordPress
 */

namespace XWP\HTTP;

use Oblak\WP\Decorators\Action;
use Oblak\WP\Decorators\Filter;
use Oblak\WP\Decorators\Hookable;
use Oblak\WP\Traits\Accessible_Hook_Methods;
use Sunrise\Http\Message\ServerRequestFactory;
use Sunrise\Http\Router\Middleware\JsonPayloadDecodingMiddleware;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;
use XWP\Decorator\Core\Controller;
use XWP\Filter\Exception_Filter;

use function Sunrise\Http\Router\emit;

/**
 * Adds the ability to route requests to the appropriate controller.
 */
#[Hookable( hook: 'parse_request', conditional: 'xwp_is_doing_ajax' )]
class Request_Dispatcher {
    use Accessible_Hook_Methods;

    /**
     * Array of controllers that will be used to handle ajax requests.
     *
     * @var array<Controller>
     */
    protected readonly array $controllers;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * The base for the ajax requests.
     *
     * @var string
     */
    protected readonly string $base;

    /**
     * Constructor
     */
    public function __construct() {
        ! \defined( 'DOING_AJAX' ) && \define( 'DOING_AJAX', true );
        ! \defined( 'WP_ADMIN' ) && \define( 'WP_ADMIN', true );
        ! \defined( 'XWP_AJAX' ) && \define( 'XWP_AJAX', true );

        \xwp_invoke_hooked_methods( $this );

        $this->base        = '/' . \get_option( 'xwp_ajax_slug', 'wp-ajax' );
        $this->router      = new Router();
        $this->controllers = $this->get_controllers();
    }

    /**
     * Get the controllers that will be used to handle ajax requests.
     *
     * @return array<Controller>
     */
    final protected function get_controllers(): array {
        /**
         * Filters the controllers that will be used to handle ajax requests.
         *
         * @param array<class-string> $controllers The controllers that will be used to handle ajax requests.
         * @param Dispatcher          $dispatcher The dispatcher instance.
         *
         * @var array<Controller> $controllers
         */
        return \apply_filters( 'xwp_ajax_controllers', array(), $this );
    }

    /**
     * Remap controllers to a more usable format.
     *
     * @param  array $ctrs The controllers to remap.
     * @return array
     */
    #[Filter( 'xwp_ajax_controllers', 'PHP_INT_MAX' )]
    protected function remap_controllers( array $ctrs ): array {
        foreach ( $ctrs as $i => $ctr ) {
            $ctr        = new \ReflectionClass( $ctr );
            $ctrs[ $i ] = \current( \xwp_get_hook_decorators( $ctr, Controller::class ) )?->set_handler( $ctr );
        }

        return \array_filter( $ctrs );
    }

    /**
     * Route the request to the appropriate controller.
     */
    #[Action( 'parse_request', 11 )]
    protected function route() {
        $collector = new RouteCollector();

        $this->router->addMiddleware( new Exception_Filter() );
        $this->router->addMiddleware( new JsonPayloadDecodingMiddleware() );

        foreach ( $this->controllers as $ctr ) {
            $this->router->addRoute(
                ...$collector->group( static fn( $c ) => $ctr->register( $c ) )
                    ->addPrefix( $ctr->get_prefix( $this->base ) )
                    ->prependMiddleware( ...$ctr->get_middlewares() )->all(),
            );
        }

        $req = ServerRequestFactory::fromGlobals()->withAttribute( '@User', \wp_get_current_user() );
        $res = $this->router->run( $req );

        emit( $res );
        die;
    }
}
