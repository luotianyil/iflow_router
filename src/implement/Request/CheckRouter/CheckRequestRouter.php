<?php

namespace iflow\Router\implement\Request\CheckRouter;

use iflow\Router\implement\Request\CheckRouter\Traits\CheckMissRouter;

class CheckRequestRouter {

    use CheckMissRouter;

    protected array $checkProcess = [
        'regx', 'domain', 'method', 'ext'
    ];

    protected array $router = [];

    protected string $url = '';

    // 请求方法
    protected string $method = '';

    // 路由请求尾缀
    protected string $ext = '';

    protected string $domain = '';


    /**
     * 验证路由信息
     * @param array $router 验证路由
     * @param string $url 请求地址
     * @param string $method 请求方法
     * @param string $domain 请求域名
     * @return bool|array
     */
    public function check(array $router, string $url, string $method, string $domain = ''): bool|array {
        $this->router = $router;
        $this->ext = pathinfo($url, PATHINFO_EXTENSION);
        $this->url = str_replace(strrchr($url, '.'), '', $url);
        $this->domain = $domain;

        $this->method = $method;
        if ($this->url === $this -> router['rule']) unset($this->checkProcess['regx']);

        foreach ($this->checkProcess as $checkProcess) {
            if (call_user_func(
                [$this, $checkProcess],
                explode('/', trim($this->router['rule'], '/')),
                explode('/', trim($this->url, '/'))
            ) === false) return false;
        }

        return $this->router;
    }

    /**
     * 正则验证
     * @param array $rule
     * @param array $url
     * @return bool
     */
    protected function regx(array $rule, array $url): bool {
        if (count($rule) !== count($url)) return false;
        $ruleIsSuccess = true;
        foreach ($url as $urlKey => $urlValue) {
            $ruleRegx = preg_replace('/[<|>]/', '', $rule[$urlKey]);
            $ruleRegx = explode(':', $ruleRegx);

            // path/article/<[0-9]{1,8}:id>/<?:groupId>
            if (count($ruleRegx) === 1 && $ruleRegx[0] !== $urlValue) {
                $ruleIsSuccess = false;
                break;
            }

            // 验证是否需要正则验证
            if ($ruleRegx[0] === '?') {
                $this->router['parameter'][$ruleRegx[1]] = [
                    'type' => ['mixed'],
                    'name' => $ruleRegx[1],
                    'default' => $urlValue
                ];
            } else if (count($ruleRegx) > 1) {
                if (
                    is_string($ruleRegx[0]) &&
                    !str_starts_with($ruleRegx[0], '/') &&
                    !preg_match('/\/[imsU]{0,4}$/', $ruleRegx[0])
                ) {
                    $ruleRegx[0] = '/^'. $ruleRegx[0] .'$/';
                }
                if (!preg_match($ruleRegx[0], $urlValue)) {
                    $ruleIsSuccess = false;
                    break;
                }

                $this->router['parameter'][$ruleRegx[1]] = [
                    'type' => ['mixed'],
                    'name' => $ruleRegx[1],
                    'default' => $urlValue
                ];
            }
        }
        return $ruleIsSuccess;
    }

    /**
     * 域名验证
     * @return bool
     */
    protected function domain(): bool {
        if (
            count($this->router['domain']) === 0 || in_array('*', $this->router['domain'])
        ) return true;
        return in_array($this->domain, $this->router['domain']);
    }

    /**
     * 验证请求方法
     * @return bool
     */
    protected function method(): bool {
        if (in_array('*', $this->router['method'])) return true;
        return in_array(strtolower($this->method), $this->router['method']);
    }

    /**
     * 路由尾缀验证
     * @return bool
     */
    protected function ext(): bool {
        if (in_array('*', $this->router['ext'])) return true;
        return in_array($this->ext, $this->router['ext']);
    }

}