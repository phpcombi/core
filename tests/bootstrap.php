<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

// set temp dir & init nette tester
define('TEMP_DIR', __DIR__ . '/tmp/' . getmypid());
require __DIR__ . '/init_tester.php';

// init combi
const TESTING = true;

include __DIR__ . '/init_package.php';
core::up('test', require __DIR__.'/test_package/env.php');
