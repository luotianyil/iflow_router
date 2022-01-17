<?php

namespace Iflow\Router\implement\Request\GenerateRouters\Parameters;

use iflow\Container\Container;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;

class GenerateRouterParameters {

    // 路由方法
    protected ReflectionFunctionAbstract $method;

    // 路由参数
    protected array $parameters = [];
    protected array $routerParameters = [];

    /**
     * @param array $parameters
     * @return GenerateRouterParameters
     */
    public function setParameters(array $parameters): GenerateRouterParameters {
        $this->parameters = $parameters;
        return $this;
    }

    // 获取路由方法参数
    public function getRouterMethodParameter(ReflectionFunctionAbstract $method): array {
        $this->method = $method;
        $this->parameters = $this->nextParameter();
        return [
            $this->parameters,
            $this->routerParameters
        ];
    }

    // 遍历方法参数
    protected function nextParameter(): array {
        $parameters = $this->method -> getParameters();
        $parameter = [];

        foreach ($parameters as $param) {
            $type = Container::getInstance() -> getParameterType($param);
            $name = $param -> getName();
            $typeName = $type[0] ?? '';
            if ($typeName === 'mixed') {
                $parameter[$name] = [
                    'type' => $type,
                    'name' => $name,
                    'default' => $this->getParamDefault($param, ['mixed'])
                ];
                continue;
            }

            if (class_exists($typeName)) {
                if (empty($this->routerParams[$typeName])) $this->getClassParams($typeName);
                $parameter[$name] = [
                    'type' => [ 'class' ],
                    'class' => $typeName,
                    'name' => $name
                ];
                continue;
            }
            $parameter[$name] = [
                'type' => $type, 'name' => $name,
                'default' => $this->getParamDefault($param, $type)
            ];
        }

        return $parameter;
    }

    /**
     * 如果是类参数则遍历 获取类参数
     * @param $className
     * @throws ReflectionException
     */
    public function getClassParams($className): void {
        // 反射实例化类
        $parametersType = new ReflectionClass($className);
        $parametersTypeInstance = $parametersType -> newInstance();
        $this->routerParameters[$className] = [];
        // 遍历 public 参数
        foreach ($parametersType -> getProperties() as $param) {
            $paramName = $param -> getName();
            $defaultValue = $parametersType -> getProperty($paramName);
            if ($defaultValue -> isPublic()) {
                $paramType = Container::getInstance() -> getParameterType($param);

                $params = [
                    'type' => $paramType,
                    'class' => $parametersTypeInstance::class,
                    'name' => $paramName,
                    'default' => $this -> getParamDefault($param, $paramType)
                ];

                if (class_exists($paramType[0])) {
                    $params['class'] = $paramType[0];
                    $params['type'] = ['class'];
                }

                if (class_exists($paramType[0]) && $paramType[0] !== $className) {
                    $this -> getClassParams($paramType[0]);
                }
                $this->routerParameters[$className][$param -> getName()] = $params;
            }
        }
    }

    // 获取参数默认值
    protected function getParamDefault(\ReflectionProperty|\ReflectionParameter $param, string|array $type): mixed {
        $isDefault = $param instanceof \ReflectionProperty?
            $param -> isDefault() : $param -> isDefaultValueAvailable();
        $default = $isDefault ? $param -> getDefaultValue() : '';
        if ($default !== '') return $default;
        $type = is_string($type) ? [$type] : $type;

        if (class_exists($type[0])) {
            return Container::getInstance() -> make($type[0], isNew: true);
        }
        return match ($type[0]) {
            'mixed', 'string' => '',
            'int', 'float' => 0.00,
            'array' => [],
            'bool' => true
        };
    }

}