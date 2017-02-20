<?php
namespace Combi;

// runtime构建
$runtime = Core\Runtime::instance();
require __DIR__ . DIRECTORY_SEPARATOR . 'helpers.php';

// 创建core包
$package = Package::instance(__DIR__);
$runtime->register($package);

// 环境扩展
require __DIR__ . DIRECTORY_SEPARATOR . 'dependencies.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'hooks.php';
