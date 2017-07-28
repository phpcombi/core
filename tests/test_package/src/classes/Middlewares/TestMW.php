<?php

namespace Test\Middlewares;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

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
        helper::du('test mw in');
        $result = $next($params, $result);
        helper::du('test mw out');
        return $result;
    }

}
