<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
namespace XWP\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sunrise\Http\Message\Stream\PhpTempStream;
use XWP\Interfaces\Response_Type;

class JSON implements Response_Type {
    public function __construct(
        /**
         * The JSON encoding options.
         *
         * @var int
         */
        private readonly int $json_flags = 0,
        /**
         * The maximum depth.
         *
         * @var int
         */
        private readonly int $json_depth = 512,
    ) {
    }

    public function create_stream( mixed $data, ResponseInterface &$res ): StreamInterface {
        if ( $data instanceof StreamInterface ) {
            return $data;
        }

        try {
            $serialized = \wp_json_encode( $data, $this->json_flags | \JSON_THROW_ON_ERROR, $this->json_depth );
        } catch ( \JsonException $e ) {
            throw new \Sunrise\Http\Message\Exception\InvalidArgumentException(
                \sprintf(
                    'Unable to create JSON response due to invalid JSON data: %s',
                    \esc_html( $e->getMessage() ),
                ),
            );
        }

        $res = $res->withHeader( 'Content-Type', 'application/json; charset=utf-8' );
        $str = new PhpTempStream( 'r+b' );
        $str->write( $serialized );
        $str->rewind();

        return $str;
    }
}
