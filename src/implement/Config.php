<?php

namespace iflow\Router\implement;

class Config {

    protected array $defaultConfig = [
        // 路由前缀
        'routerPrefix' => [],
        'swagger' => [
            'info' => [
                'title' => 'Application Apis',
                'version' => '0.0.1'
            ],
            // 服务器列表
            'server' => []
        ]
    ];

    protected array $routers = [
        'routerPrefix' => [],
        'router' => [],
        'routerParams' => [],
        'missRouter' => []
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

    /**
     * 获取路由前缀
     * @return array
     */
    public function getRouterPrefix(): array {
        return $this->config['routerPrefix'] ?? [];
    }


    /**
     * 获取缓存配置
     * @return array
     */
    public function getCache(): array {
        return $this->config['cache'] ?? [];
    }
}