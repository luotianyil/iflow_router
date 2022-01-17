<?php

namespace iflow\Router\implement\Swagger;

use iflow\Container\Container;
use iflow\Router\implement\Config;

class Swagger {

    protected array $swaggerJson = [
        'openapi' => '3.0.1',
        'info' => [
            'title' => 'Application Apis',
            'version' => '0.0.1'
        ],
        // 服务器列表
        'server' => [],
        // API 地址
        'paths' => [],
        'components' => [
            'schemas' => []
        ]
    ];

    protected array $routers = [];

    protected object $config;

    public function __construct() {
        $this -> config = Container::getInstance() -> make(Config::class);
        $this -> routers = $this -> config -> getRouters();
        $this->swaggerJson = array_merge($this->swaggerJson, $this -> config -> getSwagger());
    }

    /**
     * 生成SwaggerApiJson格式Api列表信息
     * @return array
     */
    public function buildSwaggerApiJson(): array {
        foreach ($this->routers['router'] as $routerKey => $routerValue) {
            foreach ($routerValue as $pathKey => $pathValue) {
                $pathValue['parameter'] = $this->getParameters($pathValue);
                $pathValue['rulePath'] =
                    '/'.str_replace('>', '}', str_replace('<', '{',trim($pathValue['rule'], '/') ?: ''));
                $pathValue['tags'] = [$routerKey];
                $pathValue['description'] = $pathValue['options']['description'] ?? '暂无接口描述';
                $this->swaggerJson['paths'][$pathValue['rulePath']] = $this->getRouterMethods($pathValue);
            }
        }
        return $this -> swaggerJson;
    }

    /**
     * 生成单挑路由请求数据
     * @param array $router
     * @return array
     */
    protected function getRouterMethods(array $router = []): array {
        if (in_array('*', $router['method'])) $router['method'] = [ 'get', 'post', 'delete', 'head', 'put', 'patch' ];
        $routerInfo = [];
        foreach ($router['method'] as $method) {
            if (!array_key_exists($method, $routerInfo)) {
                $routerInfo[$method] = $router;
                if (strtoupper($method) !== 'GET') {
                    $routerInfo[$method]['requestBody'] = [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/'.md5($router['rule'])
                                ]
                            ]
                        ],
                        'required' => true
                    ];
                } else {
                    $routerInfo[$method]['parameters'] = $router['parameter'];
                }
            }
        }
        return $routerInfo;
    }

    /**
     * 获取当前路由参数
     * @param array $router
     * @return array
     */
    protected function getParameters(array $router = []): array {
        $parameters = [];
        foreach ($router['parameter'] as $parameterName => $parameter) {
            if ($parameter['type'][0] === 'class') {
                $parameters[] = [
                    'name' => $parameterName,
                    'schema' => [
                        'type' => 'object'
                    ],
                    'properties' => $this->getClassParameters($parameter)
                ];
            } else {
                $parameters[] = [
                    'name' => $parameterName,
                    'schema' => [
                        'type' => $parameter['type']
                    ]
                ];
            }
        }


        $componentsSchemas = [];
        foreach ($parameters as $parameterName => $parameter) {
            $componentsSchemas[$parameter['name']] = [
                'type' => $parameter['schema']['type'],
                'properties' => []
            ];

            $properties = $parameter['properties'] ?? [];
            foreach ($properties as $property) {
                if (empty($property['name'])) continue;
                $componentsSchemas[$parameter['name']]['properties'][$property['name']] = [
                    'type' => $property['schema']['type'][0] === 'string' ? '' : 'object'
                ];
            }
        }

        $this->swaggerJson['components']['schemas'][md5($router['rule'])] = [
            'type' => 'object',
            'properties' => $componentsSchemas
        ];

        return $parameters;
    }

    /**
     * 获取类参数
     * @param array $parameters
     * @return array
     */
    protected function getClassParameters(array $parameters = []): array {
        $parameterInfo = [];
        $selfClass = $parameters['class'];
        $parameters = $this->routers['routerParams'][$selfClass];
        foreach ($parameters as $parameter) {
            if ($parameter['type'][0] === 'class' && $parameter['class'] !== $selfClass) {
                $parameterInfo[] = $this->getClassParameters($parameter);
                continue;
            }

            if ($parameter['type'][0] !== 'class') {
                $parameterInfo[] = [
                    'name' => $parameter['name'],
                    'schema' => [
                        'type' => $parameter['type']
                    ]
                ];
            }
        }
        return $parameterInfo;
    }
}