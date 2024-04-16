<?php
namespace XWP\Decorator\HTTP;

use XWP\Enums\Request_Method;
use XWP\Interfaces\Response_Type;

#[\Attribute( \Attribute::TARGET_METHOD )]
class Delete extends Method {
    public function __construct(
        string $path,
        string|Response_Type|null $type = null,
    ) {
        parent::__construct( method: Request_Method::DELETE, path: $path, type: $type );
    }
}
