<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * Res decorator file.
 *
 * @package eXtended WordPress
 * @subpackage Decorator
 */

namespace XWP\Decorator\HTTP;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\ResponseFactory;
use XWP\Enums\Request_Field;

/**
 * Response field decorator.
 *
 * Injects the response instance into the controller method.
 *
 * ! ACHTUNG ACHTUNG !
 * Using this decorator means you will have to emit a response yourself - no response modifications will be done for you.
 */
#[\Attribute( \Attribute::TARGET_PARAMETER )]
class Res extends Field {
    public function __construct(
        /**
         *  Should we passthru the response object along the chain?
         *
         * @var bool
         */
        protected bool $passthru = false,
    ) {
        parent::__construct( '', Request_Field::Response );
    }

    public function get_value( ServerRequestInterface &$req ): ResponseInterface {
        if ( ! $this->passthru ) {
            $req = $req->withAttribute( '@withResponseObject', true );
        }

        return ( new ResponseFactory() )->createResponse();
    }
}
