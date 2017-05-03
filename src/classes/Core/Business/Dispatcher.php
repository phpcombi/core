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
 *  $result = $dispatcher->call($command, [$params, [$result]]);
 *  ```
 *
 *  -   外层access调用：
 *
 *  ```php
 *  $stack = $dispatcher->callMiddlewareStack($command);
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
     * @var Dispatch\Mapping
     */
    protected $mapping = null;

    /**
     * @var \Adarts\Dictionary
     */
    protected $mapping_dict = null;

    /**
     * @param callable|string $builder
     */
    public function __construct(callable $builder = null) {
        // TODO 这里要加上路由缓存
        if ($builder) {
            $mapping = $this->getMapping();
            if (rt::isProd()) {
                $mapping->load($builder);
            } else {
                $builder($mapping);
            }
        }
    }

    /**
     * 根据rpc字串调用方法
     *
     * @param string $command
     * @param Params $params
     * @param Result $params
     * @return Result
     */
    public function call(string $command,
        Params $params = null, Result $result = null): Result
    {
        !$params && $params = new Params();
        !$result && $result = new Result();

        $stack = $this->callMiddlewareStack($command);

        return $stack($params, $result);
    }

    protected function getMapping(): Dispatch\Mapping {
        !$this->mapping && $this->mapping = new Dispatch\Mapping($this);
        return $this->mapping;
    }

    /**
     * 根据字串调度
     *
     * @param string $command
     * @return array
     */
    protected function dispatch(string
     $command): array {
        $mapping = $this->getMapping();
        $command = $mapping($command);

        // 获取action
        $pos = strrpos($command, '\\');
        if ($pos && $pos < (strlen($command) - 1)) {
            $action  = lcfirst(substr($command, $pos + 1));
            $group   = substr($command, 0, $pos);
        } else {
            $action  = 'default';
            $group   = $command[-1] == '\\' ? substr($command, 0, -1) : $command;
        }

        return [$group, $action];
    }

    /**
     * for traits middleware aware
     */
    protected function setMiddlewareStackKernel(
        Middleware\Stack $stack, string $command): void
    {
        // 先做一次调度，获取group和action
        [
            $group,
            $action,
        ] = $this->dispatch($command);

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