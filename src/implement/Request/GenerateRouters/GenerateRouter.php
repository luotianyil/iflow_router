<?php

namespace iflow\Router\implement\Request\GenerateRouters;

use iflow\Helper\Str\Str;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunctionAbstract;
use Reflector;

class GenerateRouter {

    use GenerateRouterTrait;


    /**
     * 获取当前对象方法所定义的路由
     * @param ReflectionFunctionAbstract $reflectionFunctionAbstract
     * @param ReflectionClass|Reflector $reflectionClass
     * @param array $domain
     * @return array
     */
    public function generateRouter(
        ReflectionFunctionAbstract $reflectionFunctionAbstract,
        ReflectionClass|Reflector $reflectionClass,
        array $domain
    ): array {

        [ $routers, $parameter ] = $this->initRefRouter($reflectionFunctionAbstract);

        $domain = array_merge($this -> getDomain($reflectionFunctionAbstract), $domain);

        $reflectionFunctionRouter = [];
        $refMissRouter = [];
        $refMissGlobalRouter = [];

        // 处理当前 方法路由注解
        foreach ($this->getReflectionFunctionAbstractAnnotations($reflectionFunctionAbstract) as $functionAbstractAnnotation) {
            $routerAnnotation = $functionAbstractAnnotation -> newInstance();
            $router = $this->getRequestRouter(
                $routerAnnotation,
                "{$reflectionClass -> getName()}@{$reflectionFunctionAbstract -> getName()}",
                $routerAnnotation -> getRule() ?: Str::humpToLower($reflectionFunctionAbstract -> getName()),
                $domain
            );
            $router['parameter'] = array_merge($parameter, $router['parameter']);

            // 如果是 MISS 路由
            if (in_array('MISS', $router['method']) || in_array('miss', $router['method'])) {
                $refMissRouter = $router;
                if ($routerAnnotation -> isGlobal()) $refMissGlobalRouter = $refMissRouter;
            }

            // 验证路由是否存在
            if (count($reflectionFunctionRouter) === 0) {
                $reflectionFunctionRouter[] = $router;
                continue;
            }

            // 验证 请求方法是否存在
            $checkSuccess = false;
            foreach ($reflectionFunctionRouter as &$routerValue) {
                if ($routerValue['rule'] !== $router['rule']) continue;
                $routerValue['method'] = array_merge($router['method'], $routerValue['method']);
                $checkSuccess = true;
            }

            if (!$checkSuccess) $reflectionFunctionRouter[] = $router;
        }

        foreach ($reflectionFunctionRouter as $router) {
            $routers['router'][$this->routerConfigKey][$this -> parentRule][] = $router;
        }

        if (!empty($refMissRouter)) {
            $routers['missRouter'][$this->routerConfigKey][$refMissRouter['mappingRule'] ?: $this -> parentRule] = $refMissRouter;
        }

        if (!empty($refMissGlobalRouter)) {
            $routers['missRouter'][$this->routerConfigKey]['*'] = $refMissGlobalRouter;
        }

        return $this->config -> setRouters($routers) -> getRouters();
    }

    /**
     * 获取当前路由方法注解
     * @param ReflectionFunctionAbstract $reflectionFunctionAbstract
     * @return ReflectionAttribute[]
     */
    protected function getReflectionFunctionAbstractAnnotations(
        ReflectionFunctionAbstract $reflectionFunctionAbstract
    ): array {
        $reflectionFunctionAnnotations = [];
        foreach ($this->annotations as $annotation) {
            // 获取方法注解
            $reflectionFunctionAnnotation = $reflectionFunctionAbstract -> getAttributes($annotation);
            $reflectionFunctionAnnotation = $reflectionFunctionAnnotation[0] ?? '';
            if ($reflectionFunctionAnnotation) $reflectionFunctionAnnotations[] = $reflectionFunctionAnnotation;
        }

        return $reflectionFunctionAnnotations;
    }

}