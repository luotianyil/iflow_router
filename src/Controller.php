<?php

namespace Iflow\Router;

use iflow\Container\Container;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\Router\implement\Config;
use iflow\Router\implement\Request\GenerateRouters\GenerateRouter;
use iflow\Router\implement\Request\RequestMapping;
use Attribute;
use iflow\Router\implement\Utils\Tools\StrTools;
use ReflectionClass;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller extends RequestMapping {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    protected Container $container;
    protected Reflector $reflectionClass;

    #[Inject]
    protected Config $config;

    #[Inject]
    protected StrTools $strTools;

    #[Inject]
    protected GenerateRouter $generateRouter;

    protected string $routerConfigKey = '';
    protected array $routers = [];

    protected string $rule = '';
    protected array $domain = [];

    public function process(Reflector $reflector, &$args): Controller {
        $this->reflectionClass = $reflector;
        return $this -> getControllerRouter() -> initializerControllerMethodRouter();
    }

    public function __make(Container $container, ReflectionClass $reflectionClass) {
        $this->container = $container;
        $this->routers = $this->config -> getRouters();
    }


    /**
     * 设置控制器路由数据
     * @return $this
     */
    protected function getControllerRouter(): Controller {
        $this->rule = $this->generateRouter -> getRouterPrefix(
            $this -> rule ?: $this->strTools -> humpToLower($this -> reflectionClass -> getShortName())
        );
        $this->domain = $this->generateRouter -> getDomain($this -> reflectionClass);
        return $this;
    }

    /**
     * 初始化类方法注解
     * @return $this
     */
    protected function initializerControllerMethodRouter(): Controller {
        foreach ($this->reflectionClass -> getMethods() as $method) {
            if (!$method -> isPublic()) $method -> setAccessible(true);
            $router = $this->generateRouter
                -> setParentRule($this -> rule)
                -> generateRouter($method, $this -> reflectionClass, $this -> domain);
            $this -> config -> setRouters($router);
        }
        return $this;
    }
}