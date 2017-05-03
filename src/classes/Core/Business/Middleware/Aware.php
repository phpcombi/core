<?php

namespace Combi\Core\Business\Middleware;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

/**
 * Description of Aware
 *
 * @author andares
 */
trait Aware {
    /**
     * @param array $middlewares
     * @return void
     */
    public static function addMiddlewares(...$middlewares): void
    {
        $stack = static::getMiddlewareStack();
        foreach ($middlewares as $middleware) {
            $stack->append($middleware);
        }
    }

    /**
     * @return Stack
     */
    public static function getMiddlewareStack(): Stack {
        return Stack::instance(static::class);
    }

    /**
     *
     * @param  array $arguments
     * @return Stack
     */
    public function callMiddlewareStack(...$arguments): Stack
    {
        $stack  = Stack::instance(static::class);
        $this->setMiddlewareStackKernel($stack, ...$arguments);
        return $stack;
    }

    /**
     * @param Stack $stack
     * @param array $arguments
     * @return void
     */
    abstract protected function setMiddlewareStackKernel(Stack $stack,
        ...$arguments): void;

}
