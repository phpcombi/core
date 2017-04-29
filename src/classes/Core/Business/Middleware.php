<?php

namespace Combi\Core\Business;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

abstract class Middleware
{
    abstract protected function process(Params $params, Result $result,
        callable $next): Result;

    public function __invoke(Params $params, Result $result,
        callable $next): Result
    {
        return $this->process($params, $result, $next);
    }

    public function attach(...$awares) {
        foreach ($awares as $aware) {
            if (is_object($aware)) {
                $aware->addMiddlewares($this);
            } else {
                $aware::addMiddlewares($this);
            }
        }
    }
}