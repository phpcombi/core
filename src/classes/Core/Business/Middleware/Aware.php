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
     * Add middlewares
     *
     * @param callable $middleware Any callable that accepts three arguments:
     *                           1. A Params object
     *                           2. A Result object
     *                           3. A "next" middleware callable
     */
    public static function addMiddlewares(...$middlewares)
    {
        $stack = static::getMiddlewareStack();
        foreach ($middlewares as $middleware) {
            $stack->append($middleware);
        }
    }

    public static function getMiddlewareStack(): Stack {
        return Stack::instance(static::class);
    }

    /**
     *
     * @param  Params $params A request object
     * @param  Result $res A response object
     *
     * @return Result
     */
    public function callMiddlewareStack(...$arguments): Stack
    {
        $stack  = Stack::instance(static::class);
        $this->setMiddlewareStackKernel($stack, ...$arguments);
        return $stack;
    }

    abstract protected function setMiddlewareStackKernel(Stack $stack,
        ...$arguments): void;

}
