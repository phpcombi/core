<?php

namespace Combi\Core;

use Combi\Traits;
use Combi\Meta;
use Combi\Base\Container;
use Nette\DI;

/**
 * Description of Package
 * container/service + config
 *
 * @author andares
 */
abstract class Package extends Container {
    use Traits\Instancable,
        Traits\GetNamespace,
        Meta\Overloaded;

    /**
     * @var array
     */
    protected $_path = [];

    /**
     * @var DI\Container
     */
    protected $_di = null;

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
     * @return void
     */
    public function run(): void {
        combi()->ready();
    }

    /**
     * 获取package的全局唯一id
     *
     * @return string
     */
    public function pid(): string {
        $namespace = \combi\get_namespace(static::class);
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

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call(string $name, array $arguments = []) {
        if (!$this->_di) {
            $loader = new DI\ContainerLoader($this->path('tmp', 'di'),
                !combi()->is_prod());

            $class = $loader->load(function($compiler) {
                $config = $this->config('services')->raw();
                $compiler->addConfig(
                    (new DI\Config\Adapters\NeonAdapter)->process($config));
            });
            $this->_di = new $class;
        }
        return $this->_di->getService($name);
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
