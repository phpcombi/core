<?php

namespace Combi\Core;

use Combi\Traits;

/**
 * Description of Package
 * container/service + config
 *
 * @author andares
 */
abstract class Package {
    use Traits\Instancable;

    /**
     *
     * @var Container
     */
    protected $container = null;

    public function uri() {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        return strtolower(str_replace('\\', '_', $namespace));
    }

    public function container(string $name) {
        !$this->container && $this->container = new Container;
    }

    public function __get(string $name) {
        // service与resource均由属性重载获得
    }

    abstract public function bootstrap(): bool;
}
