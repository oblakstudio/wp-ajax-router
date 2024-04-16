<?php
namespace XWP\Decorator\HTTP;

use XWP\Enums\Request_Method;
use XWP\Interfaces\Response_Type;

#[\Attribute( \Attribute::TARGET_METHOD )]
class Patch extends Method {
    public function __construct(
        string $path,
        string|Response_Type|null $type = null,
    ) {
        parent::__construct( method: Request_Method::PATCH, path: $path, type: $type );
    }
}
