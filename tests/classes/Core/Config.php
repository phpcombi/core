<?php
namespace Combi\Core;

$_root_ = realpath(__DIR__ . '/../../..');

require "$_root_/tests/bootstrap.php";

combi()->setup([
    'scene' => 'prod',
]);

$config = new Config('config.main',
    combi()->dir(__DIR__),
    combi()->dir(TEMP_DIR . '/config'));
var_dump($config->toArray());
var_dump($config->value);

$config = new Config('config.main',
    combi()->dir(__DIR__),
    combi()->dir(TEMP_DIR . '/config'));
$config->replace([
    'value' => 'man',
    'service' => [
        'b' => mt_rand(1, 999999),
    ],
]);
var_dump($config->value);
var_dump($config->service['b']);

die(1);
