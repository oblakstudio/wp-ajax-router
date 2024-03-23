<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
namespace XWP\Decorator\HTTP\Response;

use Psr\Http\Message\ResponseInterface;
use XWP\Interfaces\Response_Modifier;

#[\Attribute( \Attribute::TARGET_METHOD )]
class Code implements Response_Modifier {
    public function __construct(
        /**
         * The HTTP status code.
         *
         * @var int
         */
        protected readonly int $code,
        /**
         * The reason phrase.
         *
         * @var string
         */
        protected readonly string $reason = '',
    ) {
    }

    public function modify( ResponseInterface $res ): ResponseInterface {
        return $res->withStatus( $this->code, $this->reason );
    }
}
