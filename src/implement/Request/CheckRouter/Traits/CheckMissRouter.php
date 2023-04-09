<?php

namespace iflow\Router\implement\Request\CheckRouter\Traits;

trait CheckMissRouter {

    protected array $missRouter;

    /**
     * 获取MISS路由表
     * @param array $missRouter MISS 路由配置
     * @param string $url
     * @return array
     */
    public function getMissRouter(array $missRouter, string $url): array {
        $this->missRouter = $missRouter;

        $refRouter = '';
        $refRouterLength = 0;
        foreach ($missRouter as $routerKey => $router) {
            $routerKeyArr = explode('/', ltrim('/'.$routerKey, '/'));

            $endStr = substr($url, strlen($routerKey), 1);
            if (str_starts_with($url, $routerKey) && ($endStr === '/' || $endStr === '') && count($routerKeyArr) > $refRouterLength) {
                $refRouter = $routerKey;
                $refRouterLength = count($routerKeyArr);
            }
        }

        if ($refRouter === '') return $this->getGlobalRouter();
        return $this->missRouter[$refRouter];
    }


    /**
     * 获取全局路由
     * @return array
     */
    protected function getGlobalRouter(): array {
        return $this->missRouter['*'] ?? [];
    }

}
