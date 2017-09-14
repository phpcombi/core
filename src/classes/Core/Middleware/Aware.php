<?php

namespace Combi\Core\Middleware;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 * Description of Aware
 *
 * @author andares
 */
trait Aware {
    protected $_middleware_stack = null;
    protected static $_static_middleware_stack = null;

    /**
     * @param Stack $stack
     * @param array $arguments
     * @return void
     */
    abstract protected function setMiddlewareStackKernel(
        $stack, ...$arguments): void;

    /**
     * 添加中间件
     *
     * @param array $middlewares ...
     * @return array
     */
    public function addMiddlewares(...$middlewares): array
    {
        $stack = $this->getMiddlewareStack(true);
        foreach ($middlewares as $middleware) {
            $stack->append($middleware);
        }
        return $middlewares;
    }

    /**
     * 添加静态中间件
     *
     * @param array $middlewares ...
     * @return array
     */
    public static function addStaticMiddlewares(...$middlewares): array
    {
        $stack = static::getStaticMiddlewareStack(true);
        foreach ($middlewares as $middleware) {
            $stack->append($middleware);
        }
        return $middlewares;
    }

    /**
     *
     * @param  array $arguments
     * @return Stack
     */
    public function callMiddlewareStack(...$arguments): Stack
    {
        $stack  = static::getStaticMiddlewareStack();
        if ($stack) {
            $instance_stack = $this->getMiddlewareStack();
            if ($instance_stack) {
                $stack->kernel($instance_stack);
                $this->setMiddlewareStackKernel($instance_stack, ...$arguments);
            } else {
                $this->setMiddlewareStackKernel($stack, ...$arguments);
            }
        } else {
            $stack  = $this->getMiddlewareStack();
            $this->setMiddlewareStackKernel($stack, ...$arguments);
        }
        return $stack;
    }

    /**
     * @return Stack
     */
    public function getMiddlewareStack(bool $auto_create = false): ?Stack {
        return $this->_middleware_stack
            ?: ($auto_create
                ? ($this->_middleware_stack = new Stack()) : null);
    }

    /**
     * @return Stack
     */
    public static function getStaticMiddlewareStack(
        bool $auto_create = false): ?Stack
    {
        return static::$_static_middleware_stack
            ?: ($auto_create
                ? (static::$_static_middleware_stack = new Stack()) : null);
    }

}
