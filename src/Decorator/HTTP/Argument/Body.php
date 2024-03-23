<?php
/**
 * Body class decorator file.
 *
 * @package eXtended WordPress
 */

namespace XWP\Decorator\HTTP\Argument;

use XWP\Decorator\HTTP\Field;
use XWP\Enums\Request_Field;
use XWP\Interfaces\Pipe_Transform;

/**
 * Route handler parameter decorator.
 *
 * Extracts the entire `body` object from the `request` object and populates the decorated parameter with the value of `Body`.
 *
 * For example:
 * ```php
 * public function do_something(#[Body] $data) {}
 * ```
 *
 * @see [Request](https://extended.wp.rs/ajax/controllers#request-object)
 */
#[\Attribute( \Attribute::TARGET_PARAMETER )]
class Body extends Field {
    /**
     * Constructor.
     *
     * @param  string                $key      The parameter key.
     * @param  string|Pipe_Transform ...$pipes The pipes to apply to the parameter value.
     */
    public function __construct( string $key = '', string|Pipe_Transform ...$pipes ) {
        parent::__construct( $key, Request_Field::Body, ...$pipes );
    }
}
