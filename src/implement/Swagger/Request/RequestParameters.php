<?php

namespace Iflow\Router\implement\Swagger\Request;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestParameters extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return null;
    }

}