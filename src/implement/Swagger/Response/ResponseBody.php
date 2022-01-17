<?php

namespace Iflow\Router\implement\Swagger\Response;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_METHOD)]
class ResponseBody extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    /**
     * Swagger 响应参数注解
     * @param Reflector $reflector
     * @param ...$args
     * @return mixed
     */
    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return null;
    }
}