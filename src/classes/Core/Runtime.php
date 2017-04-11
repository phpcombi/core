<?php

namespace Combi\Core;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Traits;
use Combi\Meta;


/**
 * # 框架运行时对象
 *
 * 自动创建唯一单例。
 *
 * @author andares
 */
class Runtime extends Meta\Container {
    use Traits\Singleton,
        Meta\Extensions\Overloaded;

    /**
     * @var array
     */
    public static $_singletons = [];

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
     * @var array
     */
    private $_bootloads = [];

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
     * @param string|null $key
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
    public function register(Package $package, ...$bootload): self {
        $pid = $package->pid();

        if (!$pid) {
            throw abort::runtime('package id of {class} can not be null')
                    ->set('class', get_class($package));
        }
        if ($this->has($pid)) {
            throw abort::runtime('{class} package id {pid} is conflicted')
                    ->set('class', get_class($package))
                    ->set('pid', $pid);
        }
        $this->set($pid, $package);

        $this->_bootloads[$pid] = $bootload;
        return $this;
    }

    /**
     * @param string $pid
     * @param array $config
     * @return self
     */
    public function ready(string $pid, ?array $config = null): self {
        // 检查状态
        if ($this->_is_ready) {
            $this->bootload();
            return $this;
        }

        // 基础配置
        $config && $this->_config = array_merge($this->_config, $config);

        // 设置主包
        $this->_main_package = $this->$pid;

        // 启动引导载入
        $this->bootload();

        // 开启catcher
        $this->enableCatcher();

        // 勾子
        $this->core->hook->take(\Combi\HOOK_READY);

        // 设置状态
        $this->_is_ready = true;
        return $this;
    }

    private function bootload() {
        foreach ($this->_bootloads as $pid => $bootload) {
            foreach ($bootload as $filename) {
                require $this->$pid->path('src', "$filename.php");
            }
            unset($this->_bootloads[$pid]);
        }
    }

    /**
     * @return void
     */
    private function enableCatcher(): void {
        $config = $this->core->config('tris')['catcher'];
        if ($config && isset($config['provider']) && $config['provider']) {
            $class  = $config['provider'];
            $class::instance($config);
        }
    }
}
