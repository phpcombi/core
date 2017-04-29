<?php

namespace Combi\Core\Business;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Common\Traits;
use Combi\Common\Interfaces;
use Combi\Core\Resource;

/**
 *  # Example
 *
 *  -   直接调用，会触发路径上所有中间件:
 *
 *  ```php
 *  $result = $dispatcher->call($rpc, [$params, [$result]]);
 *  ```
 *
 *  -   外层access调用：
 *
 *  ```php
 *  $stack = $dispatcher->callMiddlewareStack($rpc);
 *  $parent_stack->kernel($stack);
 *  $parent_stack($params, $result);
 *  ```
 *
 *  如果外层也是标准aware的话可以写在setMiddlewareKernel()中。
 *
 */
class Dispatcher implements Interfaces\LinkPackage
{
    use Traits\InstanceControl,
        Traits\LinkPackage,
        Middleware\Aware;

    /**
     * @var array
     */
    protected $mappings = [];

    /**
     * @var \Adarts\Dictionary
     */
    protected $mapping_dict = null;

    /**
     * @var string
     */
    protected $current_space    = '';

    /**
     * @var string
     */
    protected $current_prefix   = '';

    /**
     * @param callable|string $build
     */
    public function __construct($build) {
        // TODO 这里要加上路由缓存
        if (is_callable($build)) {
            $build($this);
        } else {
            // 这里build为字串，即msghub路由配置名
        }
    }

    /**
     * @param string $space
     * @return self
     */
    public function space(string $space = ''): self {
        $this->current_space = $space;
        return $this;
    }

    /**
     * @param string $prefix
     * @return self
     */
    public function prefix(string $prefix = ''): self {
        $this->current_prefix = $prefix;
        return $this;
    }

    /**
     * 映射别名
     *
     * @param string $alias
     * @param string $class
     * @return self
     */
    public function mapping(string $alias, string $class): self {
        $this->current_prefix   && $alias   = "$this->current_prefix.$alias";
        $this->current_space    && $class = "$this->current_space\\$class";
        $this->mappings[$alias] = $class;
        return $this;
    }

    /**
     * 根据rpc字串调用方法
     *
     * @param string $rpc
     * @param Params $params
     * @param Result $params
     * @return Result
     */
    public function call(string $rpc,
        Params $params = null, Result $result = null): Result
    {
        !$params && $params = new Params();
        !$result && $result = new Result();

        $stack = $this->callMiddlewareStack($rpc);

        return $stack($params, $result);
    }

    /**
     * 根据字串调度
     *
     * @param string $rpc
     * @return array
     */
    protected function dispatch(string $rpc): array {
        // mapping替换
        if ($this->mappings) {
            $dict = $this->getMappingDict();

            $state = $dict->seek($rpc, 1)->current();
            if ($state) {
                $alias  = $dict->getWordByState($state);
                $rpc    = str_replace($alias, $this->mappings[$alias], $rpc);
            }
        }

        // 获取action
        $pos = strrpos($rpc, '.', -2);
        if ($pos) {
            $action  = substr($rpc, $pos + 1);
            $group = substr($rpc, 0, $pos);
        } else {
            $action  = 'default';
            $group = $rpc;
        }

        return [$group, $action];
    }

    /**
     * 获取映射字典对象
     *
     * @param bool $no_cache
     * @return \Adarts\Dictionary
     */
    protected function getMappingDict(bool $no_cache = false): \Adarts\Dictionary {
        if (!$this->mapping_dict) {
            $dir    = $this->getCacheDir();
            $maker  = function() {
                return $this->makeMappingDictCache();
            };

            $filename = $this->getCacheFilename();
            if (!$dir->writeWhenNotExists($filename, $maker)) {
                throw abort::runtime("Dispatch cache file can not create");
            }

            $this->mapping_dict = unserialize(include $dir->select($filename));
        }
        return $this->mapping_dict;
    }

    /**
     * 生成映射字典对象缓存文件
     *
     * @return string
     */
    protected function makeMappingDictCache(): string {
        if (rt::isProd()) {
            return true;
        }

        // 构建词典
        $dict = new \Adarts\Dictionary();
        foreach ($this->mappings as $alias => $replace) {
            $dict->add($alias);
        }
        $dict->confirm();

        return "<?php\nreturn '".serialize($dict)."';";
    }

    /**
     * 获取映射缓存目录
     *
     * @return Resource\Directory
     */
    protected function getCacheDir(): Resource\Directory {
        return $this->innerPackage()->dir('tmp', 'dispatcher'.
            DIRECTORY_SEPARATOR.'mapping');
    }

    /**
     * 生成映射缓存名
     *
     * @return string
     */
    protected function getCacheFilename(): string {
        return $this->innerPackage()->pid().'-'.$this->innerName().'.php';
    }

    /**
     * for traits middleware aware
     */
    protected function setMiddlewareStackKernel(
        Middleware\Stack $stack, string $rpc): void
    {
        // 先做一次调度，获取group和action
        [
            $group,
            $action,
        ] = $this->dispatch($rpc);

        $stack->kernel($this->getSubInstance($group)->callMiddlewareStack($action));
    }

    /**
     * for traits instance control
     *
     * @param string $class
     * @return Group
     */
    protected function createSubInstance($class) {
        return new $class($this);
    }
}