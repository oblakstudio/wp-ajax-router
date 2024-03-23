<?php
/**
 * Logged_In_Guard class file.
 *
 * @package eXtended WordPress
 * @subpackage Guards
 */

namespace XWP\Guard;

use Psr\Http\Message\ServerRequestInterface;
use WP_User;
use XWP\Interfaces\Can_Activate;

/**
 * Checks if the user is logged in.
 */
class Logged_In_Guard implements Can_Activate {
    /**
     * User variable
     *
     * @var WP_User|false|null
     */
    public readonly WP_User|false|null $user;

    /**
     * Constructor
     *
     * @param  int|string|WP_User|null $user The user to check.
     */
    public function __construct( int|string|WP_User|null $user = null ) {
        $this->user = $this->get_user( $user );
    }

    /**
     * Get the user.
     *
     * @param  int|string|WP_User|null $user The user to get.
     * @return WP_User|false|null
     */
    protected function get_user( int|string|WP_User|null $user = null ): WP_User|false|null {
        return match ( true ) {
            \is_null( $user )   => null,
            \is_int( $user )    => \get_user_by( 'id', $user ),
            \is_email( $user )  => \get_user_by( 'email', $user ),
            \is_string( $user ) => \get_user_by( 'login', $user ),
            default             => \wp_get_current_user(),
        };
    }

    // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
    public function can_activate( ServerRequestInterface $req ) {
        $user = $req->getAttribute( '@User' );

        if ( 0 >= $user->ID ) {
            throw new \Exception( 'No user found in the request' );
        }

        if ( false === $this->user ) {
            throw new \Exception( 'User does not exist' );
        }

        if ( ! \is_null( $this->user ) && $user !== $this->user ) {
            throw new \Exception( 'User does not match the requested user' );
        }

        return true;
    }
}
