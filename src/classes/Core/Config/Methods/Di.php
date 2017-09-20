<?php

namespace Combi\Core\Config\Methods;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

use Nette\Neon\Entity;

class Di extends Core\Config\Method
{
    protected $parameters = [];
    protected $constructor;
    protected $initialize = [];

    public function __invoke() {
        // 构造 new 参数
        $params = $this->getParamsByEntity($this->constructor);
        $class  = $this->constructor->value;
        $object = new $class(...$params);

        // 执行initialize
        if ($this->initialize) {
            foreach ($this->initialize as $call) {
                $params = $this->getParamsByEntity($call);
                $object->{$call->value}(...$params);
            }
        }

        return $object;
    }

    protected function getParamsByEntity(Entity $entity): array {
        $params = [];
        foreach ($entity->attributes as $name) {
            $params[] = $this->parameters[$name] ?? $name;
        }
        return $params;
    }
}