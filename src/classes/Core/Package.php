<?php

namespace Combi\Core;

use Combi\Traits;
use Combi\Meta;
use Combi\Base\Container;

/**
 * Description of Package
 * container/service + config
 *
 * @author andares
 */
abstract class Package extends Container {
    use Traits\Instancable,
        Meta\Overloaded;

    /**
     * 待扩展的引导方法
     */
    abstract public function bootstrap(): bool;

    /**
     * 获取package的全局唯一id，在runtime中调用
     *
     * @return string
     */
    public static function pid(): string {
        static $pid = null;
        if (!$pid) {
            $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
            $pid = strtolower(str_replace('\\', '_', $namespace));
        }
        return $pid;
    }

    /**
     * 作为容器返回自身
     *
     * @return Container
     */
    public function container(): Container {
        return $this;
    }

    public function factory() {

    }

    public function resource() {

    }

    public function config() {

    }

    /**
     * 扩展重载方法__get()以支持闭包初始化
     *
     * @param string|int $key
     * @return mixed
     */
    public function __get($key) {
        $value = $this->get($key);

        if ($value instanceof \Closure) {
            $value = $value();
            $this->set($key, $value);
        }
        return $value;
    }

}
