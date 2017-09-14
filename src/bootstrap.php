<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

// 创建core包
rt::register(Package::instance(__DIR__),
    'dependencies', 'helpers', 'hooks');
