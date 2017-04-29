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

// tris::du(helper::object2array(rt::core()), 'object2array');
// throw abort::runtime('test abort');


// tris::dt(core::now(), 'dt output');
// $abort = abort::runtime('hello! {name}', 100, new \Error('gogogo'))
//     ->set('name', 'tris');
// tris::ml($abort)->exc();
// tris::du($abort);
// throw $abort;

// $trigger = function() {
//     $a = 1;
//     trigger_error('oooo', \E_USER_ERROR);
// };
// $trigger();

$rpc    = 'api.user.info';
$rpc    = 'article.content';
$rpc    = 'article.lists';
$params = new Business\Params();
$result = inner::dispatcher()->call($rpc, $params);

// include __DIR__ . '/stack_test.php';
// include __DIR__ . '/aware_test.php';

// tris::du(yaml_parse_file(inner::path('src', 'msghub/service.yml')));

['timecost' => $timecost] = tris::debugTurnOff();

die(1);