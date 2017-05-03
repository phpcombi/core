<?php

namespace Test\Middlewares;

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

/**
 * Description of Throttle
 *
 * 429: Too Many Attempts.
 * X-RateLimit-Limit, X-RateLimit-Remaining和 Retry-After
 *
 * @author andares
 */
class TestMW extends Middleware {
    private $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    protected function process(Params $params, Result $result,
        callable $next): Result
    {
        tris::du('test mw in');
        $result = $next($params, $result);
        tris::du('test mw out');
        return $result;
    }

}
