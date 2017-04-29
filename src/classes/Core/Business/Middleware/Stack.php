<?php

namespace Combi\Core\Business\Middleware;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Common\Traits;
use SplStack;

class Stack
{
    use Traits\Instancable;

    private $owner_class;

    private $stack;

    private $kernel = null;

    private $is_processing = false;

    public function __construct(string $class) {
        $this->owner_class = $class;

        $this->stack = new \SplStack;
        $this->stack->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO
            | \SplDoublyLinkedList::IT_MODE_KEEP);
    }

    public function append(callable $middleware) {
        $this->stack[] = $middleware;
    }

    public function __invoke($params, $result) {
        if (!$this->is_processing) {
            $this->is_processing = true;
            $this->stack->rewind();
        }

        if (!$this->stack->valid()) {
            $this->is_processing = false;

            if ($this->kernel) {
                $kernel = $this->kernel;
                return $kernel($params, $result);
            }
            return $result;
        }

        $middleware = $this->stack->current();
        $this->stack->next();

        // 非闭包中间件克隆执行
        !($middleware instanceof \Closure)
            && $middleware = clone $middleware;

        return $middleware($params, $result, $this);
    }

    public function kernel(callable $kernel = null): callable {
        return $this->kernel = $kernel;
    }
}