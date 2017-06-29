<?php

namespace Combi\Core\Middleware;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;


class Stack
{
    /**
     * Spl栈对象，存放中间件调用栈
     *
     * @var \SplStack
     */
    private $stack;

    private $kernel = null;

    private $is_running = false;

    /**
     * 构造器。
     *
     * 初始化中间件调用栈。
     */
    public function __construct() {
        $this->stack = new \SplStack;
        $this->stack->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO
            | \SplDoublyLinkedList::IT_MODE_KEEP);
    }

    /**
     * 往调用栈中添加中间件
     *
     * @param callable $middleware
     */
    public function append(callable $middleware) {
        $this->stack[] = $middleware;
    }

    /**
     * 执行调用栈
     *
     * @param array $params ...
     * @return mixed
     */
    public function __invoke(...$params) {
        // 未运行时初始化
        if (!$this->is_running) {
            $this->is_running = true;
            $this->stack->rewind();
        }

        // 调用栈执行结束
        if (!$this->stack->valid()) {
            $this->is_running = false;

            if ($this->kernel) {
                $kernel = $this->kernel;
                return $kernel(...$params);
            }
            return $result;
        }

        // 调用栈步进
        $middleware = $this->stack->current();
        $this->stack->next();

        // 非闭包中间件克隆执行
        !($middleware instanceof \Closure)
            && $middleware = clone $middleware;

        // 执行并返回下一个invoke
        return $middleware($this, ...$params);
    }

    /**
     * 设置调用栈内核
     *
     * @param callable $kernel
     * @return callable
     */
    public function kernel(callable $kernel = null): callable {
        return $this->kernel = $kernel;
    }
}