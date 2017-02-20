<?php

namespace Combi\Core;

use Combi\Traits;
use Combi\Meta;
use Combi\Base\Container;


/**
 * # 框架运行时对象
 *
 * 自动创建唯一单例。
 * 可通过```combi()```方法获取。
 *
 * @author andares
 */
class Runtime extends Container {
    use Traits\Instancable,
        Meta\Overloaded;

    /**
     * @var array
     */
    private $_config = [];

    /**
     * @var bool
     */
    private $_is_running = false;

    /**
     * @var Package
     */
    private $_main_package = null;

    /**
     * @return Package
     */
    public function main(): Package {
        return $this->_main_package;
    }

    /**
     * @return bool
     */
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
     * @param Package $package
     * @return self
     */
    public function register(Package $package): self {
        $pid = $package->pid();

        if ($this->has($pid)) {
            throw \abort(new \RuntimeException('package id conflict'))
                ->set('pid', $pid);
        }
        $this->set($pid, $package);
        return $this;
    }

    /**
     * @param string $pid
     * @param array $config
     * @return self
     */
    public function run(string $pid, array $config): self {
        if ($this->_is_running) {
            return $this;
        }

        // 基础配置
        $this->_config = array_merge($this->_config, $config);

        $this->ready($this->$pid);
        $this->_is_running = true;
        return $this;
    }

    /**
     *
     * @param Package $package
     * @return void
     */
    private function ready(Package $package): void {
        $this->_main_package = $package;

        $this->core->hook->take(\Combi\HOOK_READY);

        register_shutdown_function(function() {
            combi()->core->hook->take(\Combi\HOOK_SHUTDOWN);
        });
    }

}
