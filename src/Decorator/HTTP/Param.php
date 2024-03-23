<?php
/**
 * Param decorator file.
 *
 * @package eXtended WordPress
 * @subpackage Ajax Router
 */

namespace XWP\Decorator\HTTP;

use XWP\Enums\Request_Field;
use XWP\Interfaces\Pipe_Transform;

/**
 * Route handler (method) Decorator. Routes HTTP POST requests to the specified path.
 *
 * @see [Routing](https://extended.wp.rs/ajax#routing)
 */
#[\Attribute( \Attribute::TARGET_PARAMETER )]
class Param extends Field {
    /**
     * Constructor.
     *
     * @param  string                $key      The parameter key.
     * @param  string|Pipe_Transform ...$pipes The pipes to apply to the parameter value.
     */
    public function __construct( string $key = '', string|Pipe_Transform ...$pipes ) {
        parent::__construct( $key, Request_Field::Param, ...$pipes );
    }
}
