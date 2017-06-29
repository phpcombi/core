<?php

namespace Combi\Core;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

abstract class Middleware
{
    /**
     * 中间件业务逻辑入口抽象方法
     *
     * @param callable $next
     * @param array $params ...
     * @return mixed
     */
    abstract protected function handle(callable $next, ...$params);

    /**
     * Stack调用入口
     *
     * @param callable $next
     * @param array $params ...
     * @return mixed
     */
    public function __invoke(callable $next, ...$params)
    {
        return $this->handle($next, ...$params);
    }

    /**
     * 中间件绑定Aware方法
     *
     * @param array $awares ...
     * @return void
     */
    public function attach(...$awares): void {
        foreach ($awares as $aware) {
            if (is_object($aware)) {
                $aware->addMiddlewares($this);
            } else {
                $aware::addStaticMiddlewares($this);
            }
        }
    }
}