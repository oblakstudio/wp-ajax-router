<?php
/**
 * Response_Handler interface file.
 *
 * @package eXtended WordPress
 * @subpackage Interfaces
 */

namespace XWP\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Response handler interface.
 *
 * Transforms the raw response from the controller into a PSR-7 response.
 */
interface Response_Handler {
    /**
     * Returns the instance of the response handler.
     *
     * @return static
     */
    public static function instance();

    /**
     * Transforms the non-PSR-7 response into a PSR-7 response.
     *
     * @param  mixed                  $data    The data to transform.
     * @param  ServerRequestInterface $req     The PSR-7 request.
     * @param  Response_Modifier      ...$mods The response modifiers.
     * @return ResponseInterface               The PSR-7 response.
     */
    public function handle( mixed $data, ServerRequestInterface $req, Response_Modifier ...$mods ): ResponseInterface;
}
