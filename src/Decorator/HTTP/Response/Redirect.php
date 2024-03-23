<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
namespace XWP\Decorator\HTTP\Response;

use Psr\Http\Message\ResponseInterface;
use XWP\Interfaces\Response_Modifier;

#[\Attribute( \Attribute::TARGET_METHOD )]
class Redirect implements Response_Modifier {
    public function __construct(
        protected readonly ?string $location = null,
        protected readonly int $status = 302,
        protected readonly bool $unsafe = false,
    ) {
        $this->location ??= \wp_get_referer();
    }

    public function modify( ResponseInterface $res ): ResponseInterface {
        $args = \array_filter( \json_decode( $res->getBody()->getContents(), true ) ?? array() );
        $loc  = \add_query_arg( $args, $this->location );

        $cb = $this->unsafe ? 'wp_redirect' : 'wp_safe_redirect';
        $cb( $loc, $this->status, 'XWP/Decorator' );
        exit;
    }
}
