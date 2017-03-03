<?php

namespace Combi;

$hook = combi()->core->hook;

// add hook
/**
 * combi-core中的基础勾子的值，一律以:开头，常量注册在Combi空间下
 * 其包中，以<pid>:开头
 */

// ==================== Core
const HOOK_READY        = ':ready';
const HOOK_LOG          = ':log';
const HOOK_LOG_CONTEXT  = ':log/context';
const HOOK_SHUTDOWN     = ':shutdown';

$hook
    ->add(HOOK_READY)
    ->add(HOOK_LOG)
    ->add(HOOK_SHUTDOWN);

$hook->add(HOOK_LOG_CONTEXT, function(array $handlers, array $context): array {
    foreach ($handlers as $handler) {
        $context = $handler($context);
    }
    return $context;
});

// attach hook taker
// $hook->attach(HOOK_REGRESS_RESPONSE, function(Run $run) {
//
//     $slow_time = $run->settings('debug')['slow_log'];
//     if ($slow_time) {
//         $timecost = (microtime(true) - \Tracy\Debugger::$time) * 1000;
//         if ($timecost > $slow_time) {
//             $time = str_pad(number_format($timecost, 2, '.', ''), 9, '0', STR_PAD_LEFT);
//             dlog("slow logged: $time ms");
//         }
//     }
// });
