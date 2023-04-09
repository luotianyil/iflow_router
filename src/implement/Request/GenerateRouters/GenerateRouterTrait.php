<?php

namespace iflow\Router\implement\Request\GenerateRouters;

use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\Router\implement\Config;
use iflow\Router\implement\Request\DeleteMapping;
use iflow\Router\implement\Request\GenerateRouters\Parameters\GenerateRouterParameters;
use iflow\Router\implement\Request\GetMapping;
use iflow\Router\implement\Request\HeadMapping;
use iflow\Router\implement\Request\MissMapping;
use iflow\Router\implement\Request\PatchMapping;
use iflow\Router\implement\Request\PostMapping;
use iflow\Router\implement\Request\PutMapping;
use iflow\Router\implement\Request\RequestMapping;
use iflow\Router\implement\Utils\Domain;
use ReflectionFunctionAbstract;
use Reflector;

trait GenerateRouterTrait {

    protected array $annotations = [
        GetMapping::class,
        PostMapping::class,
        PutMapping::class,
        HeadMapping::class,
        DeleteMapping::class,
        RequestMapping::class,
        PatchMapping::class,
        MissMapping::class
    ];

    #[Inject]
    protected GenerateRouterParameters $generateRouterParameters;

    public function __construct( #[Inject] protected Config $config ) {}

    protected array $methods = [];

    /**
     * 上级路由
     * @var string
     */
    protected string $parentRule = "";


    protected string $routerConfigKey = 'http';

    /**
     * @param string $parentRule
     * @return GenerateRouter
     */
    public function setParentRule(string $parentRule): self {
        $this->parentRule = $parentRule;
        return $this;
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
            $router = str_replace($startStr, $this->config -> getRouters()['routerPrefix'][$prefix[1]] ?? '', $router);
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

    /**
     * @param string $routerConfigKey
     */
    public function setRouterConfigKey(string $routerConfigKey): void {
        $this->routerConfigKey = $routerConfigKey;
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
            'domain' => $domain,
            'mappingRule' => $mapping -> getRule()
        ];
    }

    /**
     * 初始化路由信息
     * @param array|ReflectionFunctionAbstract $routers
     * @return array
     */
    protected function initRefRouter(array|ReflectionFunctionAbstract $routers = []): array{

        $parameter = [];

        // 初始化路由
        if ($routers instanceof ReflectionFunctionAbstract) {
            $reflectionFunctionAbstract = $routers;

            $routers = $this->config -> getRouters();
            $parameter = $this->generateRouterParameters
                -> setParameters($this -> config -> getRouters()['routerParams'])
                -> getRouterMethodParameter($reflectionFunctionAbstract);

            $routers['routerParams'] = array_merge($routers['routerParams'], $parameter[1]);
            $parameter = $parameter[0];
        }

        if (empty($routers['router'][$this->routerConfigKey])) {
            $routers['router'][$this->routerConfigKey] = [];
            $routers['missRouter'][$this->routerConfigKey] = [];
        }

        if (empty($routers['router'][$this->routerConfigKey][$this->parentRule])) {
            $routers['router'][$this->routerConfigKey][$this->parentRule] = [];
            $routers['missRouter'][$this->routerConfigKey][$this->parentRule] = [];
        }

        return [ $routers, $parameter ];
    }

}