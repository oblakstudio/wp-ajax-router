<?php //phpcs:disable WordPress.Security.NonceVerification
/**
 * Ajax_Request class file.
 *
 * @package eXtended WordPress
 */

namespace XWP\HTTP;

use Psr\Http\Message as PsrMsg;
use Sunrise\Http\Message as Msg;

/**
 * Extends the server request to get the user and the nonce.
 */
class Ajax_Request extends Msg\ServerRequest {
    /**
     * Creates a new request from superglobals variables
     *
     * @param array<array-key, mixed>|null $server The server parameters.
     * @param array<array-key, mixed>|null $query  The query parameters.
     * @param array<array-key, mixed>|null $cookie The cookie parameters.
     * @param array<array-key, mixed>|null $files  The uploaded files.
     * @param array<array-key, mixed>|null $body   The parsed body.
     *
     * @return PsrMsg\ServerRequestInterface
     *
     * @link http://php.net/manual/en/language.variables.superglobals.php
     * @link https://www.php-fig.org/psr/psr-15/meta/
     */
    public static function from_globals(
        ?array $server = null,
        ?array $query = null,
        ?array $cookie = null,
        ?array $files = null,
        ?array $body = null,
    ): self {
        $args = \compact( 'server', 'query', 'cookie', 'files', 'body' );

        [ $server, $query, $cookie, $files, $body, $headers ] = static::get_params( ...$args );

        return new Ajax_Request(
            Msg\server_request_protocol_version( $server ),
            Msg\server_request_method( $server ),
            Msg\server_request_uri( $server ),
            $headers,
            new Msg\Stream\PhpInputStream(),
            $server,
            $query,
            $cookie,
            Msg\server_request_files( $files ),
            $body,
            array(
                '@User' => \wp_get_current_user(),
            ),
        );
    }

    /**
     * Extract the nonce from the request.
     *
     * @param  ?array $server The server parameters.
     * @param  ?array $query  The query parameters.
     * @param  ?array $cookie The cookie parameters.
     * @param  ?array $files  The uploaded files.
     * @param  ?array $body   The parsed body.
     * @return array
     */
    protected static function get_params( $server, $query, $cookie, $files, $body ): array {
        $server ??= $_SERVER;
        $query  ??= $_GET;
        $cookie ??= $_COOKIE;
        $files  ??= $_FILES;
        $body   ??= $_POST;
        $headers = Msg\server_request_headers( $server );

        $nonce_keys = array(
            'nonce',
            'wpnonce',
            'security',
            'auth',
            '_wpnonce',
        );

        $nonce = \current( \wp_array_slice_assoc( \array_merge( $_GET, $_POST, ), $nonce_keys ) );

        if ( false !== $nonce ) {
            $headers['x-wp-nonce'] = $nonce;

            $query = \wp_array_diff_assoc( $query, $nonce_keys );
            $body  = \wp_array_diff_assoc( $body, $nonce_keys );
        }

        return array( $server, $query, $cookie, $files, $body, $headers );
    }
}
