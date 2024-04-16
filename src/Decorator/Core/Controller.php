<?php //phpcs:disable Universal.Operators.DisallowShortTernary.Found, SlevomatCodingStandard.Operators.SpreadOperatorSpacing
/**
 * Controller decorator file.
 *
 * @package eXtended WordPress
 */

namespace XWP\Decorator\Core;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\RouteCollector;
use XWP\Decorator\HTTP\Field;
use XWP\Decorator\HTTP\Method;
use XWP\Hook\Reflection;
use XWP\HTTP\Response_Handler;
use XWP\Interfaces\Response_Handler as RHI;
use XWP\Interfaces\Response_Modifier;
use XWP\Middleware\Guard_Middleware;

use function Sunrise\Http\Router\emit;

/**
 * The controller decorator.
 */
#[\Attribute( \Attribute::TARGET_CLASS )]
class Controller {
    /**
     * Controller reflector.
     *
     * @var \ReflectionClass
     */
    protected \ReflectionClass $reflector;

    /**
     * Controller instance.
     *
     * @var object|null
     */
    protected static ?object $instance = null;

    /**
     * Transforms raw response into a PSR-7 response.
     *
     * @var RHI
     */
    protected RHI $handler;

    /**
     * Array of middlewares for the controller.
     *
     * @var array
     */
    protected array $middlewares = array();

    /**
     * Constructor
     *
     * @param  string                 $path    The path that the controller will handle.
     * @param  string|null            $version The version of the controller.
     * @param  class-string<RHI>|null $handler The response handler class.
     */
    public function __construct(
        /**
         * The path that the controller will handle.
         *
         * @var string
         */
        public readonly string $path = '',
        /**
         * The version of the controller.
         *
         * @var ?int
         */
        public readonly ?int $version = null,
        ?string $handler = null,
    ) {
        $this->handler = ( $handler ?? Response_Handler::class )::instance();
    }

    /**
     * Set the controller handler.
     *
     * @param  \ReflectionClass $reflector The controller reflector.
     * @return self
     */
    public function set_handler( \ReflectionClass $reflector ): self {
        $this->reflector   = $reflector;
        $this->middlewares = $this->load_middlewares( $reflector );

        return $this;
    }

    /**
     * Load the specific middlewares for the controller.
     *
     * @param  \Reflector $r The reflector to load the middlewares for.
     * @return array
     */
    protected function load_middlewares( ?\Reflector $r = null ): array {
        $mw = array(
            'auth' => Guard_Middleware::class,
        );

        $mw = \array_map(
            static fn( $dt ) => new $dt(
                ...Reflection::get_decorators(
                    $r ?? $this->reflector,
                    $dt::DECORATOR_CLASS,
                ) ?: array(),
            ),
            $mw,
        );

        return $mw;
    }

    /**
     * Get the routes for the controller.
     *
     * @return \Generator
     */
    public function get_routes(): \Generator {
        foreach ( $this->reflector->getMethods() as $method ) {
            $req_method = Reflection::get_decorator( $method, Method::class );
            $path       = '' === $req_method->path ? '' : "/{$req_method->path}";
            yield array(
                'attributes'     => array(
                    '@Method' => $method,
                    '@Type'   => $req_method->type ?? false,
                ),
                'methods'        => $req_method->method->format(),
                'requestHandler' => $this->handle( ... ),
                'middlewares'    => $this->load_middlewares( $method ),
                'name'           => "{$this->reflector->getName()}::{$method->getName()}",
                'path'           => $path,
            );
        }
    }

    /**
     * Get the middlewares for the controller.
     *
     * @return array<MiddlewareInterface>
     */
    public function get_middlewares(): array {
        return \array_values( $this->middlewares );
    }

    /**
     * Registers routes for a single controller.
     *
     * @param  RouteCollector $c The route collector (passed by reference).
     */
    public function register( RouteCollector &$c ): void {
        $routes = $this->get_routes();

        foreach ( $routes as $route ) {
            $c->route( ...$route );
        }
    }

    /**
     * Get the prefix for the controller.
     *
     * @param  string $ajax_base The base for the ajax requests.
     * @return string
     */
    public function get_prefix( string $ajax_base ) {
        $version = $this->version ? 'v' . $this->version : '';
        return \implode( '/', \array_filter( array( $ajax_base, $this->path, $version ) ) );
    }

    /**
     * Handles the request for a given controller and method.
     *
     * Handler works in a following manner:
     * 1. We get the `@method` attribute from the request.
     * 2. We get the needed injection attributes.
     * 3. We call the controller method with injected arguments.
     * 4. If we injected a response, we emit and die, or we call a response handler.
     *
     * @param  ServerRequestInterface $req The request object.
     */
    public function handle( ServerRequestInterface $req ) {
        static::$instance ??= $this->reflector->newInstance();

        /**
         * Reflection method.
         *
         * @var \ReflectionMethod
         */
        $method = $req->getAttribute( '@Method' );
        $args   = $this->get_args( $method, $req );

        $res = static::$instance->{$method->getName()}( ...$args );

        // If we injected the response object, emit it and die.
        if ( $req->getAttribute( '@withResponseObject', false ) ) {
            emit( $res );
            die;
        }

        return $this->handler->handle( $res, $req, ...$this->get_modifiers( $method ) );
    }

    /**
     * Get the modifiers for the method.
     *
     * @param  \ReflectionMethod $method The method to get the modifiers for.
     * @return array<Response_Modifier>
     */
    protected function get_modifiers( \ReflectionMethod $method ): array {
        $mods = $method->getAttributes( Response_Modifier::class, \ReflectionAttribute::IS_INSTANCEOF );

        return \array_map( static fn( $m ) => $m->newInstance(), $mods );
    }

    /**
     * Get controller method arguments.
     *
     * @param  \ReflectionMethod      $m   The method to get the arguments for.
     * @param  ServerRequestInterface $req The request object.
     * @return array
     */
    protected function get_args( \ReflectionMethod $m, ServerRequestInterface &$req ): array {
        $args = array();

        foreach ( $m->getParameters() as $param ) {
            $decorator = \array_filter(
                \array_map(
                    static fn( $d ) => $d?->newInstance() ?? false,
                    $param->getAttributes( Field::class, \ReflectionAttribute::IS_INSTANCEOF ),
                ),
            )[0] ?? null;

            $args[ $param->getName() ] = $decorator?->get_value( $req ) ?? null;
        }

        return $args;
    }
}
