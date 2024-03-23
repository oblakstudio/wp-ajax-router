<?php
namespace XWP\Decorator\HTTP\Argument;

use XWP\Decorator\HTTP\Field;
use XWP\Enums\Request_Field;

/**
 * Route handler parameter decorator.
 *
 * Extracts the `headers` property from the `request` object and populates the decorated parameter with the value of `Headers`.
 *
 * For example:
 * ```php
 * public function do_something(#[Headers('Content-Type')] $content_type) {}
 * ```
 *
 * @see [Request](https://extended.wp.rs/ajax/controllers#request-object)
 */
#[\Attribute( \Attribute::TARGET_PARAMETER )]
class Headers extends Field {
    /**
     * Constructor.
     *
     * @param string $property Name of single header property to extract.
     */
    public function __construct( string $property = '' ) {
        parent::__construct( $property, Request_Field::Headers );
    }
}
