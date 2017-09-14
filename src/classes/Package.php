<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

use Nette\DI;

/**
 * Description of Package
 * container + config
 *
 * @author andares
 */
abstract class Package extends Core\Meta\Container {
    use Core\Traits\Singleton,
        Core\Meta\Extensions\Overloaded {}

    /**
     * @var string
     */
    protected static $_pid;

    /**
     * @var array
     */
    protected $_path = [];

    /**
     * @var DI\Container
     */
    protected $_di = null;

    /**
     * @var Core\Dictionary[]
     */
    protected $_dictionaries = [];

    /**
     * @var Core\Config[]
     */
    protected $_configs = [];

    /**
     *
     * @var Core\Logger[]
     */
    protected $_loggers = [];

    /**
     * @param string $pid
     * @param string $src_path
     */
    public function __construct(string $src_path) {
        $this->_path['src'] = $src_path;
    }

    /**
     *
     * @return string
     */
    public function pid(): string {
        return static::$_pid;
    }

    /**
     * @param string $category
     * @param string|null $path
     * @return Resource\Directory
     */
    public function dir(string $category,
        ?string $path = null): Core\Resource\Directory
    {
        return Core\Resource\Directory::instance($this->path($category, $path));
    }

    public function dict(string $name, $key = null, ...$values) {
        $locale = rt::core()->config('settings')->locale;

        // 取字典对象
        if (!isset($this->_dictionaries[$locale][$name])) {
            $tmp_dir = $this->dir('tmp', 'i18n.'.$locale.'.'.$this->pid());

            // 继承 main package 覆盖
            // 仅在非main package时处理
            if (rt::main()->pid() != $this->pid()) {
                $dictionary = new Core\Dictionary(
                    $name,
                    rt::main()->dir('src', 'i18n'.
                        DIRECTORY_SEPARATOR.$locale.
                        DIRECTORY_SEPARATOR.$this->pid()),
                    rt::env('scene'),
                    $tmp_dir);
            }

            // 尝试访问main package的覆盖配置
            if (isset($dictionary) && $dictionary->raw()) {
                $this->_dictionaries[$name] = $dictionary;
            } else {
                $this->_dictionaries[$name] = new Core\Dictionary(
                    $name,
                    $this->dir('src', 'i18n'.DIRECTORY_SEPARATOR.$locale),
                    rt::env('scene'),
                    $tmp_dir);
            }
        }

        // 根据参数处理
        if ($key) {
            return $this->_dictionaries[$name]->translate($key, ...$values);
        }
        return $this->_dictionaries[$name];
    }

    /**
     * @param string $name
     * @return Core\Config
     */
    public function config(string $name): Core\Config {
        if (!isset($this->_configs[$name])) {
            $tmp_dir = $this->dir('tmp', 'config.'.$this->pid());

            // 继承 main package 覆盖
            // 仅在非main package时处理
            if (rt::main()->pid() != $this->pid()) {
                $config = new Core\Config(
                    $name,
                    rt::main()->dir('src', 'config'.
                        DIRECTORY_SEPARATOR.$this->pid()),
                    rt::env('scene'),
                    $tmp_dir);
                }

            // 尝试访问main package的覆盖配置
            if (isset($config) && $config->raw()) {
                $this->_configs[$name] = $config;
            } else {
                $this->_configs[$name] = new Core\Config(
                    $name,
                    $this->dir('src', 'config'),
                    rt::env('scene'),
                    $tmp_dir);
            }
        }
        return $this->_configs[$name];
    }

    public function logger(string $channel) {
        if (!isset($this->_loggers[$channel])) {
            $config = $this->config('logger');
            if ($config) {
                // 包有设logger.neon但是未设对应的channel，则该channel获得一个NullLogger
                $this->_loggers[$channel] =
                    new Core\Logger($channel, $config->$channel);
            } else {
                // 包未设logger.neon配置，则使用core包的日志
                $this->_loggers[$channel] = rt::core()->logger($channel);
            }
        }
        return $this->_loggers[$channel];
    }

    /**
     * @param string $category
     * @param string|null $path
     * @return string
     */
    public function path(string $category, ?string $path = null): string {
        $prefix = $this->_path[$category] ??
            rt::env('path')[$category] ?? '';

        return $path ? ($prefix.DIRECTORY_SEPARATOR.$path) : $prefix;
    }

    /**
     * 扩展get()以支持闭包初始化
     *
     * @param string|int $key
     * @return mixed
     */
    public function get($key) {
        $value = parent::get($key);

        if ($value instanceof \Closure) {
            $value = $value();
            $this->set($key, $value);
        }
        return $value;
    }


    /**
     *
     * @param int|string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value): self {
        if (is_object($value) && $value instanceof Core\Interfaces\LinkPackage) {
            $value->linkPackage($this);
        }

        $this->_data[$key] = $value;
        return $this;
    }

}
