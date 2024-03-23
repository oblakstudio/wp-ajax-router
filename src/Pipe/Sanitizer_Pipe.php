<?php //phpcs:disable SlevomatCodingStandard.Operators, Squiz.Commenting
namespace XWP\Pipe;

use Oblak\WP\Traits\Singleton;
use XWP\Interfaces\Argument;
use XWP\Interfaces\Pipe_Transform;

/**
 * Sanitizes and unslashes the value.
 */
class Sanitizer_Pipe implements Pipe_Transform {
    use Singleton;

    /**
     * Santizes the value.
     *
     * @param  mixed    $value The value to sanitize.
     * @param  Argument $meta  The argument meta.
     * @return mixed
     */
    public function transform( mixed $value, Argument &$meta ): mixed {
        return $this->sanitize( \wp_unslash( $value ) );
    }

    /**
     * Sanitizes a value.
     *
     * Works the same as `wc_clean`
     *
     * @param  mixed $v The value to sanitize.
     * @return mixed
     */
    protected function sanitize( mixed $v ): mixed {
        return \is_array( $v )
            ? \array_map( $this->sanitize( ... ), $v )
            : \sanitize_text_field( $v );
    }
}
