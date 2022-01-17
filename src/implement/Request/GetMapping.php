<?php


namespace iflow\Router\implement\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping extends RequestMapping {
    protected string $method = "GET";
}