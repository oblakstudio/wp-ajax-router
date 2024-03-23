<?php
namespace XWP\Decorator\HTTP;

use XWP\Enums\Request_Field;
use XWP\Interfaces\Pipe_Transform;

#[\Attribute( \Attribute::TARGET_PARAMETER )]
class Query extends Field {
    /**
     * Constructor.
     *
     * @param  string                $key      The parameter key.
     * @param  string|Pipe_Transform ...$pipes The pipes to apply to the parameter value.
     */
    public function __construct( string $key = '', string|Pipe_Transform ...$pipes ) {
        parent::__construct( $key, Request_Field::Query, ...$pipes );
    }
}
