<?php
namespace Combi\Utils;

$_root_ = realpath(__DIR__ . '/../../..');

require "$_root_/tests/bootstrap.php";

$ac = new ArrayCover([
    'aa'    => [
        1, [55,66,77], 'cc' => 3
    ],
    100,
    json_decode(json_encode(['q1' => 'vvv'])),
]);

$result = $ac([
    'aa' => [
        10, 'cc' => ['qqq'], 'eee' => ['bbb'],
    ],
    'www',
    json_decode(json_encode(['q2' => 'www'])),
]);

var_export($result);

die(1);
