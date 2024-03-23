<?php //phpcs:disable PHPCompatibility.Variables.ForbiddenThisUseContexts

namespace XWP\Enums;

enum Request_Method: string {
    case GET     = 'GET';
    case POST    = 'POST';
    case PUT     = 'PUT';
    case DELETE  = 'DELETE';
    case PATCH   = 'PATCH';
    case ALL     = 'ALL';
    case OPTIONS = 'OPTIONS';
    case HEAD    = 'HEAD';
    case SEARCH  = 'SEARCH';

    public function format(): array {
        return array( $this->value );
    }
}
