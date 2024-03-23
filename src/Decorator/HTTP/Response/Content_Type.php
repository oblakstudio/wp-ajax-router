<?php
/**
 * Content-Type header decorator file.
 *
 * @package eXtended WordPress
 */

namespace XWP\Decorator\HTTP\Response;

/**
 * Sets the `Content-Type` header.
 */
#[\Attribute( \Attribute::TARGET_METHOD )]
class Content_Type extends Header {
    /**
     * Constructor.
     *
     * @param  string $value The value of the `Content-Type` header.
     */
    public function __construct( string $value = 'text/html' ) {
        parent::__construct( 'Content-Type', $value );
    }
}
