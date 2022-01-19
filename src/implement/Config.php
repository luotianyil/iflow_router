<?php

namespace iflow\Router\implement;

class Config {

    protected array $defaultConfig = [
        // 路由配置标识KEY
        'key' => 'router',
        // 路由前缀
        'routerPrefix' => [],
        'swagger' => [
            'info' => [
                'title' => 'Application Apis',
                'version' => '0.0.1'
            ],
            // 服务器列表
            'server' => [],
        ]
    ];

    protected array $routers = [
        'key' => 'router',
        'routerPrefix' => [],
        'router' => [],
        'routerParams' => []
    ];

    public function __construct(protected array $config = []) {
        $this->config = array_merge($this->defaultConfig, $this->config);
    }

    /**
     * @param array $routers
     * @return Config
     */
    public function setRouters(array $routers): Config {
        $this->routers = $routers;
        return $this;
    }

    /**
     * @return array
     */
    public function getRouters(): array {
        return $this->routers;
    }

    /**
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }

    /**
     * 获取Swagger 配置信息
     * @return array
     */
    public function getSwagger(): array {
        return $this->config['swagger'];
    }
}