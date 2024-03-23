<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * Capability_Guard class file.
 *
 * @package eXtended WordPress
 */

namespace XWP\Guard;

use Psr\Http\Message\ServerRequestInterface;
use XWP\Interfaces\Can_Activate;

/**
 * Checks if the user has a specific capability.
 */
class Capability_Guard implements Can_Activate {
    public function __construct(
        /**
         * Capability to test for.
         *
         * @var string
         */
        private string $cap,
    ) {
    }

    public function can_activate( ServerRequestInterface $req ) {
        /**
         * User variable
         *
         * @var \WP_User|false|null
         */
        $user = $req->getAttribute( '@User' );

        if ( ! $user ) {
            throw new \Exception( 'No user found in the request' );
        }

        if ( ! $user->has_cap( $this->cap ) ) {
            throw new \Exception( 'User does not have the requested capability' );
        }

        return true;
    }
}
