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
    use Meta\Overloaded;

    /**
     * @var array
     */
    protected $_path = [];

    /**
     * @var \stdClass[]
     */
    protected $_services = [];

    /**
     * @var Factory
     */
    protected $_factory = null;

    /**
     * @var Config[]
     */
    protected $_configs = [];

    /**
     * @param string $src_path
     */
    public function __construct(string $src_path) {
        $this->_path['src'] = $src_path;
    }

    /**
     * 待扩展的引导方法
     *
     * @return bool
     */
    abstract public function bootstrap(): bool;

    /**
     * 获取package的全局唯一id
     *
     * @return string
     */
    public function pid(): string {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        return strtolower(str_replace('\\', '_', $namespace));
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

    /**
     * @param string $category
     * @param ?string $path
     * @return Resource\Directory
     */
    public function dir(string $category, ?string $path = null): Resource\Directory {
        return combi()->dir($this->path($category, $path));
    }

    /**
     * @param string $name
     * @return Config
     */
    public function config(string $name): Config {
        !isset($this->_configs[$name]) &&
            $this->_configs[$name] = new Config(
                $name,
                $this->dir('src', 'config'),
                $this->dir('tmp', 'config')
            );

        return $this->_configs[$name];
    }

    /**
     * @param string $category
     * @param ?string $path
     * @return string
     */
    public function path(string $category, ?string $path = null): string {
        $prefix = $this->_path[$category] ??
            combi()->config()['path'][$category] ?? '';

        return $path ? ($prefix . DIRECTORY_SEPARATOR . $path) : $prefix;
    }

    public function __call(string $name, array $arguments = []) {

    }

    public static function __callStatic(string $name, array $arguments = []) {

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
