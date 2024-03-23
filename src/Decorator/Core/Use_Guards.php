<?php //phpcs:disable SlevomatCodingStandard.Operators.SpreadOperatorSpacing
namespace XWP\Decorator\Core;

use Psr\Http\Message\ServerRequestInterface;
use XWP\Interfaces\Can_Activate;

#[\Attribute( \Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS )]
class Use_Guards {
    public readonly array $guards;

    public function __construct(
        callable|Can_Activate|array ...$guards,
    ) {
        $this->guards = \array_map( $this->parse_guard( ... ), $guards );
    }

    protected function parse_guard( callable|Can_Activate|array $guard ): Can_Activate {
        return match ( true ) {
            \is_array( $guard )    => \array_map( $this->parse_guard( ... ), $guard ),
            \is_callable( $guard ) => $this->create_guard( $guard ),
            default                => $guard,
        };
    }

    /**
     * Create a guard instance.
     *
     * @param  callable $guard The guard callback.
     * @return Can_Activate
     */
    protected function create_guard( callable $guard ): Can_Activate {
        return new class($guard) implements Can_Activate{
            /**
             * Constructor
             *
             * @param  callable $guard The guard callback.
             */
            public function __construct( private $guard ) {
            }

            // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
            public function can_activate( ServerRequestInterface $req ): bool {
                return ( $this->guard )( $req );
            }
        };
    }
}
