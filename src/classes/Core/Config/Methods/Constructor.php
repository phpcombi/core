<?php

namespace Combi\Core\Config\Methods;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

use Nette\Neon\Entity;

class Constructor extends Core\Config\Method
{
    protected $take = null;
    protected $deps = [];
    protected $init = [];

    public function __invoke() {
        if ($this->take && !is_array($this->take)) {
            $result = $this->getDependeny($this->take);
        } else {
            $take   = $this->take ?: array_keys($this->deps);
            if (!$this->take && count($take) == 1) {
                $result = $this->getDependeny($this->take);
            } else {
                $result = [];
                foreach ($take as $name) {
                    $result[$name] = $this->getDependeny($name);
                }
            }
        }

        if (!$result) {
            return [];
        }
        return $result;
    }

    protected function getDependeny($name) {
        if (!isset($this->deps[$name])) {
            return null;
        }

        if ($this->deps[$name] instanceof Entity) {
            $params = $this->getParamsByEntity($this->deps[$name]);
            $class  = $this->deps[$name]->value;
            if (strpos($class, '::')) {
                $class  = explode('::', $class);
                $object = $class(...$params);
            } else {
                $object = new $class(...$params);
            }

            if ($this->init && isset($this->init[$name])) {
                foreach ($this->init[$name] as $call) {
                    $params = $this->getParamsByEntity($call);
                    $object->{$call->value}(...$params);
                }
            }
            $this->deps[$name] = $object;
        }
        return $this->deps[$name];
    }

    protected function getParamsByEntity(Entity $entity): array {
        $params = [];
        foreach ($entity->attributes as $name) {
            $param    = $this->getDependeny($name);
            $params[] = $param === null ? $name : $param;
        }
        return $params;
    }
}