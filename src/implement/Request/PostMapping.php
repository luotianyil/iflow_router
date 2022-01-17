<?php

namespace iflow\Router\implement\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PostMapping extends RequestMapping {
    protected string $method = "POST";
}