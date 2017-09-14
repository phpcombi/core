<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

// 时间
$time_config = rt::core()->config('settings')->time;
rt::core()->time =
    new Core\Utils\DateTime('now', $time_config['timezone'] ?? null);
rt::core()->now  = rt::core()->time->now();

