<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Nette\DI;

/**
 * Description of Package
 * container/service + config
 *
 * @author andares
 */
abstract class Package extends core\Meta\Container {
    use core\Traits\Singleton,
        core\Meta\Extensions\Overloaded;

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
     * @var core\Dictionary[]
     */
    protected $_dictionaries = [];

    /**
     * @var core\Config[]
     */
    protected $_configs = [];

    /**
     *
     * @var core\Logger[]
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
        ?string $path = null): core\Resource\Directory
    {
        return core\Resource\Directory::instance($this->path($category, $path));
    }

    public function dict(string $name, $key = null, ...$values) {
        $locale = core::config('settings')->locale;

        // 取字典对象
        if (!isset($this->_dictionaries[$locale][$name])) {
            $tmp_dir = $this->dir('tmp', 'i18n'.
            DIRECTORY_SEPARATOR.$locale.
            DIRECTORY_SEPARATOR.$this->pid());

            // 继承 main package 覆盖
            // 仅在非main package时处理
            if (core::main()->pid() != $this->pid()) {
                $dictionary = new core\Dictionary(
                    $name,
                    core::main()->dir('src', 'i18n'.
                        DIRECTORY_SEPARATOR.$locale.
                        DIRECTORY_SEPARATOR.$this->pid()),
                    core::env('scene'),
                    $tmp_dir);
            }

            // 尝试访问main package的覆盖配置
            if (isset($dictionary) && $dictionary->raw()) {
                $this->_dictionaries[$name] = $dictionary;
            } else {
                $this->_dictionaries[$name] = new core\Dictionary(
                    $name,
                    $this->dir('src', 'i18n'.DIRECTORY_SEPARATOR.$locale),
                    core::env('scene'),
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
     * @return core\Config
     */
    public function config(string $name): core\Config {
        if (!isset($this->_configs[$name])) {
            $tmp_dir = $this->dir('tmp', 'config'.DIRECTORY_SEPARATOR.$this->pid());

            // 继承 main package 覆盖
            // 仅在非main package时处理
            if (core::main()->pid() != $this->pid()) {
                $config = new core\Config(
                    $name,
                    core::main()->dir('src', 'config'.
                        DIRECTORY_SEPARATOR.$this->pid()),
                    core::env('scene'),
                    $tmp_dir);
            }

            // 尝试访问main package的覆盖配置
            if (isset($config) && $config->raw()) {
                $this->_configs[$name] = $config;
            } else {
                $this->_configs[$name] = new core\Config(
                    $name,
                    $this->dir('src', 'config'),
                    core::env('scene'),
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
                    new core\Logger($channel, $config->$channel);
            } else {
                // 包未设logger.neon配置，则使用core包的日志
                $this->_loggers[$channel] = core::logger($channel);
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
            core::env('path')[$category] ?? '';

        return $path ? ($prefix.DIRECTORY_SEPARATOR.$path) : $prefix;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return class
     */
    public function __call(string $name, array $arguments) {
        return $this->service($name);
    }

    /**
     * @param string $name
     * @return class
     */
    public function service($name) {
        if (!$this->_di) {
            // 兼容未安装DI服务
            if (!class_exists('Nette\\DI\\Container')) {
                throw abort::badMethodCall('The method %name%@%class% not exist.')
                    ->set('name',   $name)
                    ->set('class',  static::class);
            }

            // 检查缓存目录
            $tmp_dir    = $this->path('tmp',
                'di'.DIRECTORY_SEPARATOR.core::env('scene'));

            // 载入di管理器
            $loader = new core\NetteFixer\DI\ContainerLoader($tmp_dir,
                !core::isProd());
            $class = $loader->load(function($compiler) {
                $config = $this->config('services')->raw();
                $compiler->addConfig(
                    (new DI\Config\Adapters\NeonAdapter)->process($config));
            }, $this->pid());
            $this->_di = new $class;
        }
        return $this->_di->getService($name);
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
        if (is_object($value) && $value instanceof core\Interfaces\LinkPackage) {
            $value->linkPackage($this);
        }

        $this->_data[$key] = $value;
        return $this;
    }

}
