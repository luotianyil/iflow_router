<?php

namespace iflow\Router\implement\Utils;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Domain extends AnnotationAbstract {

    public function __construct(protected array|string $domain = '*', protected array $args = []) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return null;
    }

    public function getDomain(): array {
        return is_string($this->domain) ? explode('|', $this->domain) : $this->domain;
    }

}