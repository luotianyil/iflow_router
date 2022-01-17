<?php


namespace iflow\Router\implement\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class DeleteMapping extends RequestMapping {
    protected string $method = "DELETE";
}