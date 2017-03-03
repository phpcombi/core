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
    private $_is_ready = false;

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
    public function isProd(): bool {
        return  $this->_config['is_prod'];
    }

    /**
     *
     *
     * @param ?string $key
     * @param mixed $value
     * @return mixed
     */
    public function config(?string $key = null, $value = null) {
        if ($key) {
            $value !== null && $this->_config[$key] = $value;
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
            throw abort(new \RuntimeException('package id conflict'))
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
    public function ready(string $pid, array $config): self {
        // 检查状态
        if ($this->_is_ready) {
            return $this;
        }

        // 基础配置
        $this->_config = array_merge($this->_config, $config);

        // 设置主包
        $this->_main_package = $this->$pid;

        // 勾子
        $this->core->hook->take(\Combi\HOOK_READY);

        // 设置状态
        $this->_is_ready = true;
        return $this;
    }
}
