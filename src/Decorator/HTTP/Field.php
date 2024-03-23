<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing, SlevomatCodingStandard.Operators.SpreadOperatorSpacing
namespace XWP\Decorator\HTTP;

use Psr\Http\Message\ServerRequestInterface;
use XWP\Enums\Request_Field;
use XWP\Interfaces\Argument;
use XWP\Interfaces\Pipe_Transform;
use XWP\Pipe\Sanitizer_Pipe;

/**
 * Base injection field
 */
abstract class Field implements Argument {
    /**
     * Pipes to apply to the field value.
     *
     * @var array<Pipe_Transform>
     */
    protected readonly array $pipes;

    public function __construct(
        public readonly string $key,
        public readonly Request_Field $field,
        string|Pipe_Transform ...$pipes,
    ) {
        $this->pipes = \array_filter(
            \array_map( $this->parse_pipes( ... ), $pipes ),
        ) ?: array( $this->default_pipe() ); //phpcs:ignore Universal.Operators
    }

    /**
     * Default sanitize pipe.
     *
     * @return Pipe_Transform
     */
    protected function default_pipe(): Pipe_Transform {
        return Sanitizer_Pipe::instance();
    }

    /**
     * Parses a pipe.
     *
     * @param  string|Pipe_Transform $pipe The pipe to parse.
     * @return Pipe_Transform|false        The parsed pipe, or false if the pipe is invalid.
     */
    protected function parse_pipes( string|Pipe_Transform $pipe ): Pipe_Transform|false {
        if ( ! \is_string( $pipe ) && \is_a( $pipe, Pipe_Transform::class ) ) {
            return $pipe;
        }

        if ( ! \class_exists( $pipe ) ) {
            return false;
        }

        return new $pipe();
    }

    /**
     * Apply pipes to the value.
     *
     * @param  mixed $value The value to apply the pipes to.
     * @return mixed
     */
    protected function apply_pipes( mixed $value ): mixed {
        return \array_reduce(
            $this->pipes,
            fn( $value, Pipe_Transform $pipe ) => $pipe->transform( $value, $this ),
            $value,
        );
    }

    public function get_value( ServerRequestInterface &$req ): mixed {
        $value = $this->field->callback( $req )();
        $value = '' !== $this->key ? $value[ $this->key ] ?? null : $value ?? null;

        return $this->apply_pipes( $value );
    }
}
