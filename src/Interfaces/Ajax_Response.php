<?php
/**
 * WP_Ajax_Response interface file.
 *
 * @package eXtended WordPress
 */

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for a WordPress AJAX response.
 */
interface WP_Ajax_Response extends ResponseInterface, StatusCodeInterface {
    /**
     * Create a new instance from raw response data.
     *
     * @param mixed $response The raw response data.
     *
     * @return static
     */
    public static function fromRawResponse( mixed $response ): static;

    /**
     * Get the raw response data.
     *
     * @return mixed
     */
    public function getRawResponse(): mixed;

    /**
     * Set the raw response data.
     *
     * @param mixed $response The raw response data.
     */
    public function setRawResponse( mixed $response ): void;

    /**
     * Set the raw response data and return a new instance.
     *
     * @param mixed $response The raw response data.
     *
     * @return static
     */
    public function withRawResponse( mixed $response ): static;
}
