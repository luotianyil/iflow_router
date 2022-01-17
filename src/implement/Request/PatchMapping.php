<?php

namespace iflow\Router\implement\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping extends RequestMapping {
    protected string $method = "PATCH";
}
