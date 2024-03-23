<?php
/**
 * Method class file.
 *
 * @package eXtended WordPress
 * @subpackage Ajax Router
 */

namespace XWP\Decorator\HTTP;

use XWP\Enums\Request_Method;
use XWP\Interfaces\Response_Type;

/**
 * Abstract class for HTTP method Decorator.
 */
abstract class Method {
    /**
     * The return type.
     *
     * @var Response_Type|null
     */
    public readonly ?Response_Type $type;

    /**
     * Constructor.
     *
     * @param Request_Method           $method The HTTP method.
     * @param string                   $path   The route path.
     * @param string|class-string|null $type   The return type.
     */
    public function __construct(
        public readonly Request_Method $method,
        public readonly string $path,
        string|Response_Type|null $type = null,
    ) {
        $this->type = match ( \gettype( $type ) ) {
            'string' => new $type(),
            'object' => $type,
            default => null,
        };
    }
}
