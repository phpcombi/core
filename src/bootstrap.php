<?php
namespace Combi;

// runtime构建
$runtime = Core\Runtime::instance();

// 创建core包
$runtime->register(Package::instance(__DIR__),
    'dependencies', 'hooks', 'helpers');
