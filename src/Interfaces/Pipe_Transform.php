<?php
/**
 * Pipe_Transform interface file.
 *
 * @package eXtended WordPress
 * @subpackage Interfaces
 */
namespace XWP\Interfaces;

/**
 * Shared interface for all pipes that transform values.
 */
interface Pipe_Transform {
    /**
     * Transforms the value.
     *
     * @param  mixed    $value The value to transform.
     * @param  Argument $meta  The argument meta.
     * @return mixed
     */
    public function transform( mixed $value, Argument &$meta ): mixed;
}
