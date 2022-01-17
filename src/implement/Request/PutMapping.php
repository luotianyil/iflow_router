<?php

namespace iflow\Router\implement\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PutMapping extends RequestMapping {
    protected string $method = "PUT";
}