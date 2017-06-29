<?php

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Test\Package as inner;
use Combi\Core\Abort as abort;

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

\Tester\Assert::equal(1, 1);

require __DIR__ . '/playground/middleware.php';

// exit with signal 1 to show echo message
die(1);
