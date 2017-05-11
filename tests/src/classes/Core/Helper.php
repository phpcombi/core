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

$rpc    = 'user.info';
// $rpc    = 'article.content';
// $rpc    = 'article.lists';
// $rpc    = 'article.aaa';
$params = new Business\Params();
// $result = inner::dispatcher()->call($rpc, $params);

// include __DIR__ . '/stack_test.php';
// include __DIR__ . '/aware_test.php';

// tris::du(yaml_parse_file(inner::path('src', 'msghub/service.yml')));

// $abort = abort::unexpectedValue('User {user_id} is not found', 21001)
//     ->set('user_id', 100)
//     ->set('show', 0);

// tris::du($abort);
// echo $abort;
// tris::dt($abort);


core::hook()->attach(\Combi\HOOK_LOG_PREPARE,
    function(array $record, string $to_file_path): array
{
    // 给record加上自定义参数
    $record['custom_info'] = $_REQUEST['info'] ?? null;
    return $record;
});
core::hook()->attach(\Combi\HOOK_LOG, function(array $record) {
    if ($record['custom_info']) {
        file_put_contents('/tmp/custom.log',
            $record['custom_info']."\n", \FILE_APPEND);
    }
});

$_REQUEST['info'] = 'bbbb';
tris::log('aaa');


['timecost' => $timecost] = tris::debugTurnOff();

die(1);