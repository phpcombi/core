<?php

namespace App\Middlewares;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
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
class Throttle extends Middleware {

    public $prefix = ":throttle:count:";

    private $id_fetcher;
    private $alerter;
    private $interval;
    private $limit;
    private $special_ids;

    // public function __construct(callable $id_fetcher, callable $alerter,
    //     int $interval = 60, int $limit = 20, array $special_ids = []) {

    //     $this->id_fetcher   = $id_fetcher;
    //     $this->alerter      = $alerter;
    //     $this->interval     = $interval;
    //     $this->limit        = $limit;
    //     $this->special_ids  = $special_ids;
    // }

    private function getConfigById($id): array {
        if (isset($this->special_ids[$id])) {
            return [
                $this->special_ids[$id]['interval'],
                $this->special_ids[$id]['limit'],
            ];
        }
        return [$this->interval, $this->limit];
    }

    protected function process(Params $params, Result $result,
        callable $next): Result
    {
        helper::dt('throttle mw in');
        $result = $next($params, $result);
        helper::dt('throttle mw out');
        return $result;
        // // 取id
        // $id_fetcher = $this->id_fetcher;
        // $id = $id_fetcher($request);

        // // 取配置
        // list($interval, $limit) = $this->getConfigById($id);
        // $response = $response->withHeader('X-RateLimit-Limit', $limit);

        // // 根据配置生成key，并取值运算
        // $now    = time();
        // $round  = floor($now / $interval);
        // $key    = "$this->prefix$id:$round";

        // $redis_buffer = redis('cache')->multi()
        //     ->incr($key);
        // $count = $redis_buffer ? ($redis_buffer
        //         ->expire($key, $interval)
        //         ->exec()[0]) : 0;
        // if ($count > $limit) {
        //     // 不过
        //     $alerter = $this->alerter;
        //     return $alerter($response->withHeader('X-RateLimit-Remaining', 0)
        //         ->withHeader('Retry-After', ($interval * ($round + 1)) - $now));
        // }

        // // 过
        // return $next($request, $response
        //     ->withHeader('X-RateLimit-Remaining', $limit - $count));
    }

}
