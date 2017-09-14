<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

// set temp dir & init nette tester
require __DIR__ . '/init_tester.php';

// init combi
const TESTING = true;

include __DIR__ . '/init_instance.php';
rt::up('app', require __DIR__.'/instance/env.php');
