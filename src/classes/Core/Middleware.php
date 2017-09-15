<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

abstract class Middleware
{
    /**
     * 中间件业务逻辑入口抽象方法
     *
     * @param callable $next
     * @return mixed
     */
    abstract protected function handle(callable $next);

    /**
     * Stack调用入口
     *
     * @param callable $next
     * @return mixed
     */
    public function __invoke(callable $next)
    {
        return $this->handle($next);
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