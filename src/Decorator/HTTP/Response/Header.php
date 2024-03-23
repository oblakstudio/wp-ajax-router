<?php // phpcs:disable Squiz.Commenting.FunctionComment.Missing
namespace XWP\Decorator\HTTP\Response;

use Psr\Http\Message\ResponseInterface;
use XWP\Interfaces\Response_Modifier;

#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE )]
class Header implements Response_Modifier {
    public function __construct(
        /**
         * The header name.
         *
         * @var string
         */
        protected readonly string $name,
        /**
         * The header value.
         *
         * @var string
         */
        protected readonly string $value,
        /**
         * Whether to overwrite the header if it already exists.
         *
         * @var bool
         */
        protected readonly bool $overwrite = true,
    ) {
    }

    public function modify( ResponseInterface $res ): ResponseInterface {
        return $this->overwrite
            ? $res->withHeader( $this->name, $this->value )
            : $res->withAddedHeader( $this->name, $this->value );
    }
}
