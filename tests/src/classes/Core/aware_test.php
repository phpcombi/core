<?php

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Test\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Core\Business\{
    Params,
    Result,
    Middleware
};
use Combi\Core\Business\Middleware\{
    Aware,
    Stack
};

class d
{
    use Aware;

    protected function setMiddlewareStackKernel(Stack $stack,
        g $group, string $action): void
    {
        $stack->kernel($group->callMiddlewareStack($action));
    }
}

class g
{
    use Aware;

    protected function setMiddlewareStackKernel(Stack $stack,
        string $action): void
    {
        $a = new $action;
        $stack->kernel($a->callMiddlewareStack());
    }
}

class a
{
    use Aware;

    private $params;
    private $result;

    protected function setMiddlewareStackKernel(Stack $stack): void {
        $stack->kernel($this);
    }

    public function __invoke($params, $result) {
        $this->params = $params;
        $this->result = $result;
        $this->main();
        return $this->result;
    }

    public function main() {
        tris::du($this->params);
        tris::du($this->result);
    }
}

$m1 = function($params, $result, $next) {
    tris::du(">>>>>> 111 in");
    $result = $next($params, $result);
    tris::du(">>>>>> 111 out");
    return $result;
};

$m2 = function($params, $result, $next) {
    tris::du(">>>>>> 222 in");
    $result = $next($params, $result);
    tris::du(">>>>>> 222 out");
    return $result;
};

$m3 = function($params, $result, $next) {
    tris::du(">>>>>> 333 in");
    $result = $next($params, $result);
    tris::du(">>>>>> 333 out");
    return $result;
};

$d = new d;
$g = new g;

$d->addMiddlewares($m1, $m2, $m3);
$g->addMiddlewares($m2, $m3);
a::addMiddlewares($m3);

$params = new Params;
$result = new Result;

$stack = $d->callMiddlewareStack($g, 'a');

var_dump($stack($params, $result));
