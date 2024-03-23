<?php //phpcs:disable PHPCompatibility.Variables, SlevomatCodingStandard.Operators.SpreadOperatorSpacing
namespace XWP\Enums;

use Psr\Http\Message\ServerRequestInterface;

enum Request_Field {
    case Body;
    case Query;
    case Param;
    case File;
    case Files;
    case Response;
    case Headers;

    public function callback( ServerRequestInterface &$r ): callable {
        return match ( $this ) {
            Request_Field::Body    => $r->getParsedBody( ... ),
            Request_Field::Query   => $r->getQueryParams( ... ),
            Request_Field::Param   => $r->getAttributes( ... ),
            Request_Field::File    => $r->getUploadedFiles( ... ),
            Request_Field::Files   => $r->getUploadedFiles( ... ),
            Request_Field::Headers => $r->getHeaders( ... ),
        };
    }
}
