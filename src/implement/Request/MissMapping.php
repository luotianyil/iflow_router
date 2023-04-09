<?php

namespace iflow\Router\implement\Request;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class MissMapping extends RequestMapping {

    protected string $method = 'MISS';

    protected string $ext = '*';

    protected array $parameter = [];

    public function __construct(protected bool $isGlobal = false, protected string $rule = '', protected array $options = []) {
    }


    /**
     * @return bool
     */
    public function isGlobal(): bool {
        return $this->isGlobal;
    }

}
