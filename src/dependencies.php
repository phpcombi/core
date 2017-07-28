<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

// 时间
$time_config = core::config('settings')->time;
core::instance()->time =
    new core\Utils\DateTime('now', $time_config['timezone'] ?? null);
core::instance()->now  = core::time()->now();

