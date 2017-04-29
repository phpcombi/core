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
 *  -   使用main()方法则可跳过中间件。
 *  -   使用callMiddlewareStack()方法取stack出来调用则会触发中间件。
 */
abstract class Action
{
    use Middleware\Aware;

    protected $group;

    protected $params;

    protected $result;

    abstract public function main(): void;

    public function __construct(Group $group) {
        $this->group = $group;
    }

    public function __invoke(Params $params, Result $result) {
        $this->params = $params;
        $this->result = $result;
        $this->main();
        return $this->result;
    }

    protected function setMiddlewareStackKernel(
        Middleware\Stack $stack): void
    {
        $stack->kernel($this);
    }
}