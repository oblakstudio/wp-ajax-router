<?php //phpcs:disable Squiz.Commenting
/**
 * Nonce_Guard class file.
 *
 * @package eXtended WordPress
 */

namespace XWP\Guard;

use Psr\Http\Message\ServerRequestInterface;
use XWP\Interfaces\Can_Activate;

/**
 * Prevents requests without a valid nonce.
 */
class Nonce_Guard implements Can_Activate {
    public function __construct(
        /**
         * Action to verify the nonce for.
         *
         * If `null` - will be determined by method name.
         *
         * @var string|null
         */
        private readonly ?string $action = null,
        /**
         * Nonce verification will hard-fail a request
         *
         * @var bool
         */
        private readonly bool $bail = true,
    ) {
    }

    public function can_activate( ServerRequestInterface $req ) {
        $action = $this->action ?? $req->getAttribute( '@Method' )->getName();
        $valid  = \array_reduce(
            $this->get_nonces( $req ),
            static fn( $c, $v ) => $c || \wp_verify_nonce( $v, $action ),
            false,
        );

        if ( ! $valid && $this->bail ) {
            throw new \Exception( 'Invalid nonce' );
        }

        return true;
    }

    /**
     * Get the nonces from the request.
     *
     * @param  ServerRequestInterface $req The request.
     * @return array<string>
     */
    protected function get_nonces( ServerRequestInterface $req ): array {
        $in_header = $req->getHeader( 'x-wp-nonce' )[0] ?? false;

        if ( $in_header ) {
            return array( $in_header );
        }

        $nonce_keys = array(
            'nonce',
            'wpnonce',
            'security',
            'sec-check',
            'wp-security',
            'wc-ajax',
            'wc-security',
            '_wpnonce',
            'x_wp_nonce',
            'wp-nonce',
        );

        $nonces = \wp_array_slice_assoc( $this->get_fields( $req ), $nonce_keys );

        return \array_reduce(
            $nonces,
            static fn( $c, $v ) => \array_merge( $c, \is_array( $v ) ? $v : array( $v ) ),
            array(),
        );
    }

    /**
     * Get the fields and values which may contain the nonce.
     *
     * @param  ServerRequestInterface $req The request.
     * @return array<string, string>
     */
    protected function get_fields( ServerRequestInterface $req ): array {
        $fields = \array_merge(
            $req->getHeaders(),
            $req->getParsedBody(),
            $req->getQueryParams(),
        );

        return \array_combine(
            \array_map( 'strtolower', \array_keys( $fields ) ),
            \array_values( $fields ),
        );
    }
}
