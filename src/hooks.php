<?php

namespace Combi;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

$hook = core::hook();

// add hook
/**
 * combi-core中的基础勾子的值，一律以:开头，常量注册在Combi空间下
 * 其包中，以<pid>:开头
 */

// ==================== Core
const HOOK_ACTION_BEGIN     = ':action_break';
const HOOK_ACTION_END       = ':action_end';
const HOOK_ACTION_BROKEN    = ':action_broken';

const HOOK_READY        = ':ready';
const HOOK_TICK         = ':tick';
const HOOK_LOG          = ':log';
const HOOK_LOG_CONTEXT  = ':log/context';
const HOOK_SHUTDOWN     = ':shutdown';

$hook
    ->add(HOOK_ACTION_BEGIN)
    ->add(HOOK_ACTION_END)
    ->add(HOOK_ACTION_BROKEN)

    ->add(HOOK_READY)
    ->add(HOOK_TICK)
    ->add(HOOK_LOG)
    ->add(HOOK_SHUTDOWN);

$hook->add(HOOK_LOG_CONTEXT, function(array $handlers, array $context): array {
    foreach ($handlers as $handler) {
        $context = $handler($context);
    }
    return $context;
});


// attach hook taker
// 时间
core::hook()->attach(HOOK_TICK, function() {
    core::instance()->now = core::time()->now();
});

// slowlog
if ($slowlog_limit = core::config('base')->log['slowlog_limit']) {
    core::hook()->attach(HOOK_ACTION_BEGIN, function() {
        tris::timer('__slowlog', true);
    });
    core::hook()->attach(HOOK_ACTION_END, function() use ($slowlog_limit) {
        $timecost = tris::timer('__slowlog') * 1000;
        if (timecost > slowlog_limit) {
            $time = str_pad(number_format($timecost, 2, '.', ''),
                9, '0', STR_PAD_LEFT);
            tris::ml("slowlog: $time ms")->warning();
        }
    });
}
