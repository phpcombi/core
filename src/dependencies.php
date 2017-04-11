<?php

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

// hook系统
core::instance()->hook = Combi\Core\Hook::instance();

// 时间
$time_config = core::config('base')->time;
core::instance()->time =
    new Combi\Utils\DateTime('now', $time_config['timezone'] ?? null);
core::instance()->now  = core::time()->now();
