<?php
/**
 * Utility functions for working with xwp-ajax.
 *
 * @package eXtended WordPress
 */

/**
 * Check if the current request is an xwp-ajax request.
 *
 * @return bool
 */
function xwp_is_doing_ajax(): bool {
    return '' !== ( $GLOBALS['wp']->query_vars['xwp_ajax'] ?? '' );
}

/**
 * Register the controllers that will be used to handle ajax requests.
 *
 * @param  class-string ...$controllers The controllers that will be used to handle ajax requests.
 */
function xwp_register_routes( string ...$controllers ) {
    static $xwp_ajax_server;

    $xwp_ajax_server ??= \XWP\Ajax_Server::instance();

    add_filter( 'xwp_ajax_controllers', static fn( array $ctr ) => array_merge( $ctr, $controllers ) );
}


/**
 * Get the URL for an xwp-ajax request.
 *
 * @param  string ...$parts The parts of the URL.
 * @return string
 */
function xwp_ajax_url( string ...$parts ): string {
    array_unshift( $parts, \get_option( 'xwp_ajax_slug', 'wp-ajax' ) );

    return \home_url( implode( '/', $parts ) );
}

/**
 * Disable the default ajax URL variables.
 */
function xwp_disable_ajax_js_vars() {
    static $disabled;

    $disabled ??= add_filter( 'xwp_ajax_vars', '__return_false' );
}
