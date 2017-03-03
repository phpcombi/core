<?php

namespace Combi\Core;

use Combi\Traits;
use Combi\Meta;
use Combi\Utils;
use Combi\Base\Container;
use Combi\NetteFixer\DI\ContainerLoader;
use Nette\DI;

/**
 * Description of Package
 * container/service + config
 *
 * @author andares
 */
class Package extends Container {
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
    public function __construct(string $src_path) {
        $this->_path['src'] = $src_path;
    }

    /**
     * 获取package的全局唯一id
     *
     * @return string
     */
    public function pid(): string {
        !$this->_pid && $this->_pid =
            strtolower(str_replace('\\', '_', static::namespace()));
        return $this->_pid;
    }

    /**
     * @param string $category
     * @param ?string $path
     * @return Resource\Directory
     */
    public function dir(string $category, ?string $path = null): Resource\Directory {
        return combi()->dir($this->path($category, $path));
    }

    public function dict(string $name, $key = null, ...$values) {
        $locale = combi()->config('locale');

        // 取字典对象
        if (!isset($this->_dictionaries[$locale][$name])) {
            $tmp_dir = $this->dir('tmp', 'i18n'.
            DIRECTORY_SEPARATOR.$locale.
            DIRECTORY_SEPARATOR.$this->pid());

            // 继承 main package 覆盖
            $dictionary = new Utils\Dictionary(
                $name,
                combi()->main()->dir('src', 'i18n'.
                    DIRECTORY_SEPARATOR.$locale.
                    DIRECTORY_SEPARATOR.$this->pid()), $tmp_dir);

            if ($dictionary->raw()) { // 尝试访问main package的覆盖配置
                $this->_dictionaries[$name] = $dictionary;
            } else {
                $this->_dictionaries[$name] = new Utils\Dictionary(
                    $name,
                    $this->dir('src', 'i18n'.DIRECTORY_SEPARATOR.$locale),
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
            $config = new Config(
                $name,
                combi()->main()->dir('src', 'config'.
                    DIRECTORY_SEPARATOR.$this->pid()),
                $tmp_dir);

            if ($config->raw()) { // 尝试访问main package的覆盖配置
                $this->_configs[$name] = $config;
            } else {
                $this->_configs[$name] = new Config(
                    $name,
                    $this->dir('src', 'config'),
                    $tmp_dir);
            }
        }

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

        return $path ? ($prefix.DIRECTORY_SEPARATOR.$path) : $prefix;
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call(string $name, array $arguments = []) {
        if (!$this->_di) {
            // 检查缓存目录
            $tmp_dir    = $this->path('tmp',
                'di'.DIRECTORY_SEPARATOR.combi()->config('scene'));

            // 载入di管理器
            $loader = new ContainerLoader($tmp_dir,
                !combi()->isProd());
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
