<?php

namespace iflow\Router\implement\Request\GenerateRouters;

use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\Router\implement\Config;
use iflow\Router\implement\Request\DeleteMapping;
use Iflow\Router\implement\Request\GenerateRouters\Parameters\GenerateRouterParameters;
use iflow\Router\implement\Request\GetMapping;
use iflow\Router\implement\Request\HeadMapping;
use iflow\Router\implement\Request\PatchMapping;
use iflow\Router\implement\Request\PostMapping;
use iflow\Router\implement\Request\PutMapping;
use iflow\Router\implement\Request\RequestMapping;
use Iflow\Router\implement\Utils\Domain;
use iflow\Router\implement\Utils\Tools\StrTools;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunctionAbstract;
use Reflector;

class GenerateRouter {

    protected array $annotations = [
        GetMapping::class,
        PostMapping::class,
        PutMapping::class,
        HeadMapping::class,
        DeleteMapping::class,
        RequestMapping::class,
        PatchMapping::class
    ];

    protected array $methods = [];
    protected string $parentRule = "";

    #[Inject]
    protected GenerateRouterParameters $generateRouterParameters;

    #[Inject]
    protected StrTools $strTools;

    public function __construct( #[Inject] protected Config $config ) {}

    /**
     * @param string $parentRule
     * @return GenerateRouter
     */
    public function setParentRule(string $parentRule): GenerateRouter {
        $this->parentRule = $parentRule;
        return $this;
    }


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

        $routers = $this->config -> getRouters();
        $parameter = $this->generateRouterParameters
            -> setParameters($this -> config -> getRouters()['routerParams'])
            -> getRouterMethodParameter($reflectionFunctionAbstract);

        $routers['routerParams'] = array_merge($routers['routerParams'], $parameter[1]);
        $parameter = $parameter[0];

        $domain = array_merge($this -> getDomain($reflectionFunctionAbstract), $domain);

        $reflectionFunctionRouter = [];

        if (empty($routers['router'][$this->parentRule]))
            $routers['router'][$this->parentRule] = [];

        // 处理当前 方法路由注解
        foreach ($this->getReflectionFunctionAbstractAnnotations($reflectionFunctionAbstract) as $functionAbstractAnnotation) {
            $routerAnnotation = $functionAbstractAnnotation -> newInstance();
            $router = $this->getRequestRouter(
                $routerAnnotation,
                "{$reflectionClass -> getName()}@{$reflectionFunctionAbstract -> getName()}",
                $routerAnnotation -> getRule() ?: $this->strTools -> humpToLower($reflectionFunctionAbstract -> getName()),
                $domain
            );
            $router['parameter'] = array_merge($parameter, $router['parameter']);

            // 验证路由是否存在
            if (count($reflectionFunctionRouter) === 0) {
                $reflectionFunctionRouter[] = $router;
            } else {
                $checkSuccess = false;
                foreach ($reflectionFunctionRouter as &$routerValue) {
                    if ($routerValue['rule'] !== $router['rule']) continue;
                    $routerValue['method'] = array_merge($router['method'], $routerValue['method']);
                    $checkSuccess = true;
                }
                if (!$checkSuccess) $reflectionFunctionRouter[] = $router;
            }
        }

        foreach ($reflectionFunctionRouter as $router) {
            $routers['router'][$this -> parentRule][] = $router;
        }

        return $this->config -> setRouters($routers) -> getRouters();
    }


    /**
     * 获取路由基础信息
     * @param object $mapping
     * @param string $method
     * @param string $rule
     * @param array $domain
     * @return array
     */
    public function getRequestRouter(
        object $mapping, string $method, string $rule, array $domain
    ): array {
        return [
            'rule' => str_replace('//', '/', $this->parentRule . '/' . $rule),
            'method' => $mapping -> getMethod(),
            'action' => $method,
            'ext' => $mapping -> getExt(),
            'parameter' => $mapping -> getParameter(),
            'options' => $mapping -> getOptions(),
            'domain' => $domain
        ];
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

    /**
     * 检测路由是否设置前缀
     * @param string $router
     * @return string
     */
    public function getRouterPrefix(string $router): string {
        $startStr = explode('/', $router)[0];
        preg_match("/^%(.*?)%$/", $startStr, $prefix);
        if (count($prefix) > 1) {
            $router = str_replace($startStr, $this->config['routerPrefix'][$prefix[1]] ?? '', $router);
        }
        return $router;
    }

    /**
     * 获取域名分组
     * @param Reflector $reflector
     * @return array
     */
    public function getDomain(Reflector $reflector): array {
        $domainAnnotation = $reflector -> getAttributes(Domain::class)[0] ?? '';
        if ($domainAnnotation) {
            return ($domainAnnotation -> newInstance()) -> getDomain();
        }
        return [];
    }
}