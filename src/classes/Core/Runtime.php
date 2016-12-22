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

    private $_config = [];

    /**
     *
     * @param array $config
     * @return self
     */
    public function setup(array $config): self {
        $this->_config = array_merge($this->_config, $config);
        return $this;
    }

    public function is_prod(): bool {
        return  $this->_config['is_prod'];
    }

    /**
     *
     *
     * @param ?string $key
     * @return mixed
     */
    public function config(?string $key = null) {
        if ($key) {
            return $this->_config[$key] ?? null;
        }
        return $this->_config;
    }

    /**
     * @param string $dir
     * @return Resource\Directory
     */
    public function dir(string $dir): Resource\Directory {
        return Resource\Directory::instance($dir);
    }

    /**
     * 注册一个package到runtime
     *
     * @param string $class
     * @param string $src_path
     * @return bool
     */
    public function register(string $class, string $src_path): bool {
        $pid = $class::pid();
        if ($this->has($pid)) {
            return false;
        }

        $package = $class::instance($src_path);
        if ($package->bootstrap()) {
            $this->set($pid, $package);
            return true;
        }
        return false;
    }

}
