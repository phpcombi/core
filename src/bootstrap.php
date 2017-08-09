<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

// 创建core包
core::register(core::create(__DIR__),
    'dependencies', 'hooks', 'helpers');
