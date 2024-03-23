<?php //phpcs:disable SlevomatCodingStandard.Operators.SpreadOperatorSpacing, Squiz.Commenting.FunctionComment.Missing
/**
 * Response_Handler class file.
 *
 * @package eXtended WordPress
 */

namespace XWP\HTTP;

use Oblak\WP\Traits\Singleton;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\Response;
use XWP\Interfaces\Response_Handler as Response_Handler_Interface;
use XWP\Interfaces\Response_Modifier;

/**
 * Performs the transformation of the raw response into a PSR-7 response.
 */
class Response_Handler implements Response_Handler_Interface {
    use Singleton;

    protected function __construct() {
        // Dindu nuffin.
    }

    public function handle(
        mixed $data,
        ServerRequestInterface $req,
        Response_Modifier ...$mods,
    ): ResponseInterface {
        if ( \is_a( $data, ResponseInterface::class ) ) {
            return $data;
        }

        $res  = $this->apply_modifiers( $mods, $req->getMethod() );
        $hdl  = $req->getAttribute( '@Type' );
        $data = $hdl->create_stream( $data, $res );

        return $res->withBody( $data );
    }

    /**
     * Applies the modifiers to the response.
     *
     * @param  array<Response_Modifier> $modifiers The modifiers to apply.
     * @param  string                   $method    The request method.
     * @return ResponseInterface
     */
    protected function apply_modifiers( array $modifiers, string $method ): ResponseInterface {
        return \array_reduce( $modifiers, $this->reduce( ... ), new Response( 'GET' === $method ? 200 : 201 ) );
    }

    /**
     * Performs the reduction of the response.
     *
     * @param  ResponseInterface $r The response to modify.
     * @param  Response_Modifier $m The modifier to apply.
     * @return ResponseInterface
     */
    protected function reduce( ResponseInterface $r, Response_Modifier $m, ): ResponseInterface {
        return $m->modify( $r );
    }
}
