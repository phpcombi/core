<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

$hook = rt::core()->hook();

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
$hook->attach(HOOK_TICK, function() {
    rt::core()->now = rt::core()->time->now();
});

// action error
$hook->attach(HOOK_ACTION_BROKEN,
    function(Core\Action $action, \Throwable $e)
{
    $config = rt::core()->config('settings')->debug['actionError'];
    if ($config['show']) {
        helper::du("Action Id: ".$action->getActionId(), 'Action Error Raised');
        Core\Trace\Catcher::instance()->exceptionHandler($e, false);
    }
    if ($config['halt']) {
        die(1);
    }
});

// slowLog
if ($slowLogLimit = rt::core()->config('settings')->slowLog['limit']) {
    $hook->attach(HOOK_ACTION_BEGIN, function() {
        helper::timer('__slowLog');
    });
    $hook->attach(HOOK_ACTION_END, function() use ($slowLogLimit) {
        $timecost = helper::timer('__slowLog') * 1000;
        if ($timecost > $slowLogLimit) {
            $time = str_pad(number_format($timecost, 2, '.', ''),
                9, '0', STR_PAD_LEFT);
            helper::logger('slow')->info("slowLog: $time ms");
        }
    });
}
