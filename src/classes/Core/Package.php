<?php

namespace Combi\Core;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Utils\Interfaces;
use Combi\Utils\Traits;
use Combi\Meta;
use Combi\Utils;
use Nette\DI;

/**
 * Description of Package
 * container/service + config
 *
 * @author andares
 */
class Package extends Meta\Container {
    use Traits\Instancable,
        Meta\Extensions\Overloaded;

    /**
     * @var array
     */
    protected $_path = [];

    /**
     * @var DI\Container
     */
    protected $_di = null;

    /**
     * @var Utils\Dictionary[]
     */
    protected $_dictionaries = [];

    /**
     * @var Config[]
     */
    protected $_configs = [];

    /**
     * @var string
     */
    protected $_pid = null;

    /**
     * @param string $src_path
     */
    public function __construct(string $pid, string $src_path) {
        $this->_pid = $pid;
        $this->_path['src'] = $src_path;
    }

    /**
     *
     * @return string
     */
    public function pid(): string {
        return $this->_pid;
    }

    /**
     * @param string $category
     * @param string|null $path
     * @return Resource\Directory
     */
    public function dir(string $category, ?string $path = null): Resource\Directory {
        return rt::dir($this->path($category, $path));
    }

    public function dict(string $name, $key = null, ...$values) {
        $locale = rt::config('locale');

        // 取字典对象
        if (!isset($this->_dictionaries[$locale][$name])) {
            $tmp_dir = $this->dir('tmp', 'i18n'.
            DIRECTORY_SEPARATOR.$locale.
            DIRECTORY_SEPARATOR.$this->pid());

            // 继承 main package 覆盖
            // 仅在非main package时处理
            if (rt::main() && rt::main()->pid() != $this->pid()) {
                $dictionary = new Utils\Dictionary(
                    $name,
                    rt::main()->dir('src', 'i18n'.
                        DIRECTORY_SEPARATOR.$locale.
                        DIRECTORY_SEPARATOR.$this->pid()),
                    rt::config('scene'),
                    $tmp_dir);
            }

            // 尝试访问main package的覆盖配置
            if (isset($dictionary) && $dictionary->raw()) {
                $this->_dictionaries[$name] = $dictionary;
            } else {
                $this->_dictionaries[$name] = new Utils\Dictionary(
                    $name,
                    $this->dir('src', 'i18n'.DIRECTORY_SEPARATOR.$locale),
                    rt::config('scene'),
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
     * @return Config
     */
    public function config(string $name): Config {
        if (!isset($this->_configs[$name])) {
            $tmp_dir = $this->dir('tmp', 'config'.DIRECTORY_SEPARATOR.$this->pid());

            // 继承 main package 覆盖
            // 仅在非main package时处理
            if (rt::main() && rt::main()->pid() != $this->pid()) {
                $config = new Config(
                    $name,
                    rt::main()->dir('src', 'config'.
                        DIRECTORY_SEPARATOR.$this->pid()),
                    rt::config('scene'),
                    $tmp_dir);
            }

            // 尝试访问main package的覆盖配置
            if (isset($config) && $config->raw()) {
                $this->_configs[$name] = $config;
            } else {
                $this->_configs[$name] = new Config(
                    $name,
                    $this->dir('src', 'config'),
                    rt::config('scene'),
                    $tmp_dir);
            }
        }

        return $this->_configs[$name];
    }

    /**
     * @param string $category
     * @param string|null $path
     * @return string
     */
    public function path(string $category, ?string $path = null): string {
        $prefix = $this->_path[$category] ??
            rt::config('path')[$category] ?? '';

        return $path ? ($prefix.DIRECTORY_SEPARATOR.$path) : $prefix;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return \stdClass
     */
    public function __call(string $name, array $arguments) {
        return $this->service($name);
    }

    /**
     * @param string $name
     * @return \stdClass
     */
    public function service($name): \stdClass {
        if (!$this->_di) {
            // 检查缓存目录
            $tmp_dir    = $this->path('tmp',
                'di'.DIRECTORY_SEPARATOR.rt::config('scene'));

            // 载入di管理器
            $loader = new NetteFixer\DI\ContainerLoader($tmp_dir,
                !rt::isProd());
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
        if (is_object($value) && $value instanceof Interfaces\LinkPackage) {
            $value->linkPackage($this, $key);
        }

        $this->_data[$key] = $value;
        return $this;
    }

}
