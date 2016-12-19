<?php

namespace Combi\Core;

use Combi\Traits;
use Combi\Meta;
use Combi\Base\Container;


/**
 * Description of Runtime
 *
 * @author andares
 */
class Runtime extends Container {
    use Traits\Instancable,
        Meta\Overloaded;

    /**
     * 注册一个package到runtime
     *
     * @param string $class
     * @return bool
     */
    public function register(string $class): bool {
        $pid = $class::pid();
        if ($this->has($pid)) {
            return false;
        }

        $package = $class::instance();
        if ($package->bootstrap()) {
            $this->set($pid, $package);
            return true;
        }
        return false;
    }

}
