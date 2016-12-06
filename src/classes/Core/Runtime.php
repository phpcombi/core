<?php

namespace Combi\Core;

use Combi\Traits;


/**
 * Description of Runtime
 *
 * @author andares
 */
class Runtime {
    use Traits\Instancable;

    private $packages = [];

    public function register(string $class) {
        $package = $class::instance();
        if ($package->bootstrap()) {
            $uri = $package->uri();
            $this->$uri = $package;
        }
    }

    public function __get(string $name) {
        return $this->packages[$name] ?? null;
    }

    public function __set(string $name, Package $package) {
        $this->packages[$name] = $package;
    }

    public function __isset(string $name): bool {
        return isset($this->packages[$name]);
    }
}
