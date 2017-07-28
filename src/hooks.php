<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Combi\Core\Trace\ExceptionSample;

$hook = core::hook();

// add hook
/**
 * combi-core中的基础勾子的值，一律以:开头，常量注册在Combi空间下
 * 其包中，以<pid>:开头
 */

// ==================== Core
const HOOK_ACTION_BEGIN     = 'core:action.begin';
const HOOK_ACTION_END       = 'core:action.end';
const HOOK_ACTION_BROKEN    = 'core:action.broken';

const HOOK_REDIS_UP     = 'core:redis.up';

const HOOK_READY        = 'core:ready';
const HOOK_TICK         = 'core:tick';
const HOOK_SHUTDOWN     = 'core:shutdown';

$hook
    ->add(HOOK_ACTION_BEGIN)
    ->add(HOOK_ACTION_END)
    ->add(HOOK_ACTION_BROKEN)

    ->add(HOOK_REDIS_UP)

    ->add(HOOK_READY)
    ->add(HOOK_TICK)
    ->add(HOOK_SHUTDOWN);

// attach hook taker

// 时间
core::hook()->attach(HOOK_TICK, function() {
    core::instance()->now = core::time()->now();
});

// slowlog
if ($slowlog_limit = core::config('settings')->log['slowlog_limit']) {
    core::hook()->attach(HOOK_ACTION_BEGIN, function() {
        helper::timer('__slowlog');
    });
    core::hook()->attach(HOOK_ACTION_END, function() use ($slowlog_limit) {
        $timecost = helper::timer('__slowlog') * 1000;
        if (timecost > slowlog_limit) {
            $time = str_pad(number_format($timecost, 2, '.', ''),
                9, '0', STR_PAD_LEFT);
            helper::log('slow')->info("slowlog: $time ms");
        }
    });
}
