<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

// 创建core包
core::register(Package::instance(__DIR__),
    'dependencies', 'hooks', 'helpers');
