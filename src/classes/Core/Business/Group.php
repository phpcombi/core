<?php

namespace Combi\Core\Business;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

/**
 *  # Example
 *
 *  -   默认由dispatcher调用。
 *  -   也可直接调用，同样触发路径上的中间件：
 *
 *  ```php
 *  $result = $group->$action([$params, [$result]]);
 *  ```
 */
abstract class Group
{
    use Middleware\Aware;

    protected $dispatcher;

    protected $action;

    public function __construct(Dispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function __call(string $name, array $arguments): Result {
        $params = $arguments[0] ?? new Params();
        $result = $arguments[0] ?? new Result();

        $stack = $this->callMiddlewareStack($name);
        return $stack($params, $result);
    }

    public function createAction($name): Action {
        $class = static::class.'\\'.ucfirst($name);
        $this->action = new $class($this);
        return $this->action;
    }

    /**
     * for traits middleware aware
     */
    protected function setMiddlewareStackKernel(
        Middleware\Stack $stack, string $action): void
    {
        $stack->kernel($this->createAction($action)->callMiddlewareStack());
    }

}