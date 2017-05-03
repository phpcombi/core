<?php

namespace Combi\Core;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Test\Package as inner;
use Combi\Core\Abort as abort;

use Tester\Assert;

$__root = realpath(__DIR__ . '/../../../..');
require "$__root/tests/bootstrap.php";

tris::debugTurnOn();

Assert::same(helper::namespace(rt::class), 'Combi\Facades');
Assert::same(rt::test()->pid(), 'test');

// $rpc    = 'user.info';
// $rpc    = 'article.content';
$rpc    = 'article.lists';
$rpc    = 'article.aaa';
$params = new Business\Params();
$result = inner::dispatcher()->call($rpc, $params);

// include __DIR__ . '/stack_test.php';
// include __DIR__ . '/aware_test.php';

// tris::du(yaml_parse_file(inner::path('src', 'msghub/service.yml')));

['timecost' => $timecost] = tris::debugTurnOff();

die(1);