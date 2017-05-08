<?php

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

// set temp dir & init nette tester
define('TEMP_DIR', __DIR__ . '/tmp/' . getmypid());
require __DIR__ . '/init_tester.php';

// init combi
const TESTING = true;
include __DIR__ . '/init_package.php';

rt::ready('test', [
	'scene'     => 'test',
    'is_prod'   => false,

    'path'      => [
        'tmp'   => TEMP_DIR . '/tmp',
        'logs'  => __DIR__ . '/logs',
        'docs'  => TEMP_DIR . '/docs',
        'tests' => TEMP_DIR . '/tests',
    ],
]);
