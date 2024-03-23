<?php //phpcs:disable SlevomatCodingStandard.Operators.SpreadOperatorSpacing
namespace XWP\Filter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Message\StreamFactory;
use Sunrise\Http\Router\Middleware\CallableMiddleware;

/**
 * Base exception filter.
 */
class Exception_Filter extends CallableMiddleware {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( $this->handle_exception( ... ) );
    }

    /**
     * Handle the exception.
     *
     * @param  ServerRequestInterface  $req     The request instance.
     * @param  RequestHandlerInterface $handler The request handler instance.
     * @return ResponseInterface
     */
    public function handle_exception( ServerRequestInterface $req, RequestHandlerInterface $handler ): ResponseInterface {
        try {
            return $handler->handle( $req );
        } catch ( \Sunrise\Http\Router\Exception\MethodNotAllowedException $e ) {
            return $this->create_response( 405, $e );
        } catch ( \Sunrise\Http\Router\Exception\RouteNotFoundException $e ) {
            return $this->create_response( 404, $e );
        } catch ( \Throwable $e ) {
            return $this->create_response( 500, $e );
        }
    }

    /**
     * Create a response instance.
     *
     * @param  int        $status_code The status code.
     * @param  \Throwable $e    The exception instance.
     * @return ResponseInterface
     */
    protected function create_response( int $status_code, \Throwable $e ): ResponseInterface {
        $stream = ( new StreamFactory() )->createStream( $e->getMessage() );
        return ( new ResponseFactory() )->createResponse( $status_code )->withBody( $stream );
    }
}
