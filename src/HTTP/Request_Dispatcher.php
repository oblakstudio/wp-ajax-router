<?php
/**
 * Request_Dispatcher class file.
 *
 * @package eXtended WordPress
 */

namespace XWP\HTTP;

use Sunrise\Http\Message\ServerRequestFactory;
use Sunrise\Http\Router\Middleware\JsonPayloadDecodingMiddleware;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;
use XWP\Contracts\Hook\Accessible_Hook_Methods;
use XWP\Contracts\Hook\Initialize;
use XWP\Decorator\Core\Controller;
use XWP\Filter\Exception_Filter;
use XWP\Hook\Context_Host;
use XWP\Hook\Decorators\Action;
use XWP\Hook\Decorators\Filter;
use XWP\Hook\Decorators\Handler;
use XWP\Hook\Reflection;

use function Sunrise\Http\Router\emit;

/**
 * Adds the ability to route requests to the appropriate controller.
 */
#[Handler( tag: 'parse_request', priority: 0 )]
class Request_Dispatcher {
    use Accessible_Hook_Methods;

    /**
     * Array of controllers that will be used to handle ajax requests.
     *
     * @var array<Controller>
     */
    protected array $controllers;

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

    public static function can_initialize() {
        return $GLOBALS['wp']->query_vars['xwp_ajax'] ?? false;
    }

    /**
     * Constructor
     */
    public function __construct() {
        ! \defined( 'DOING_AJAX' ) && \define( 'DOING_AJAX', true );
        ! \defined( 'WP_ADMIN' ) && \define( 'WP_ADMIN', true );
        ! \defined( 'XWP_AJAX' ) && \define( 'XWP_AJAX', true );

        $this->base   = '/' . \get_option( 'xwp_ajax_slug', 'wp-ajax' );
        $this->router = new Router();
        // $this->controllers = $this->get_controllers();
    }

    /**
     * Get the controllers that will be used to handle ajax requests.
     */
    #[Action( tag: 'parse_request', priority: 10 )]
    final protected function get_controllers() {
        /**
         * Filters the controllers that will be used to handle ajax requests.
         *
         * @param array<class-string> $controllers The controllers that will be used to handle ajax requests.
         * @param Dispatcher          $dispatcher The dispatcher instance.
         *
         * @var array<Controller> $controllers
         */
        $this->controllers = \apply_filters( 'xwp_ajax_controllers', array(), $this );
    }

    /**
     * Remap controllers to a more usable format.
     *
     * @param  array $ctrs The controllers to remap.
     * @return array
     */
    #[Filter( tag: 'xwp_ajax_controllers', priority: PHP_INT_MAX )]
    protected function remap_controllers( array $ctrs ): array {
        $reflectors = \array_map( static fn( $ctr ) => new \ReflectionClass( $ctr ), $ctrs );

        $ctrs = \array_map(
            static fn( $ctr, $rfl ) => Reflection::get_decorator(
                $ctr,
                Controller::class,
            )->set_handler( $rfl ),
            $ctrs,
            $reflectors,
        );

        return \array_filter( $ctrs );
    }

    /**
     * Route the request to the appropriate controller.
     */
    #[Action( tag: 'parse_request', priority: 11 )]
    protected function route() {
        $collector = new RouteCollector();

        $this->router->addMiddleware( new Exception_Filter() );
        $this->router->addMiddleware( new JsonPayloadDecodingMiddleware() );

        // \dump( $this->controllers );
        // die;

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
