<?php

namespace iflow\Router\implement\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class HeadMapping extends RequestMapping {
    protected string $method = "HEAD";
}