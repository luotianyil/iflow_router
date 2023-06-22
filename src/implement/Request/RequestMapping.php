<?php

namespace iflow\Router\implement\Request;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class RequestMapping extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    protected string $method = "*";

    public function __construct(
        protected string $rule = '',
        protected string $ext = '*',
        protected array $parameter = [],
        protected array $options = []
    ) {}

    /**
     * @return array
     */
    public function getMethod(): array {
        return explode('|', strtolower($this->method));
    }

    /**
     * @return string
     */
    public function getRule(): string {
        return $this->rule;
    }

    /**
     * @return array
     */
    public function getExt(): array {
        return explode('|', $this->ext);
    }

    /**
     * @return array
     */
    public function getParameter(): array {
        return $this->parameter;
    }

    /**
     * @return array
     */
    public function getOptions(): array {
        return $this->options;
    }

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return null;
    }
}