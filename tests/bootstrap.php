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

include __DIR__ . '/init_instance.php';
core::up('app', require __DIR__.'/instance/env.php');
