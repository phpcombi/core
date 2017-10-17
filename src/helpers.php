<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

helper::register(function(int $random_length = 10): string
{
    return (new Core\Utils\IdGenerator())
        ->randByLength($random_length)
        ->orderable()
        ->gmpStrval()
        ->get();
}, 'genId');

helper::register(function() {
    return (new Core\Utils\IdGenerator())
        ->orderable(1000)
        ->gmpStrval()
        ->get().
        (new Core\Utils\IdGenerator())
        ->uuid()
        ->gmpStrval(62, '0x')
        ->get();
}, 'genUuid');


helper::register(function($var, $title = null): void {
    helper::du($var, $title);
}, 'dt');

helper::register(function($var, $title = null): void {
    helper::du($var, $title);
    die(1);
}, 'dd');


helper::register(function($message, array $context = []): void {
    helper::logger('debug')->debug($message, $context);
}, 'debug');

helper::register(function(string $name = 'default'): float {
    return Core\Utils\Debug::instance()->timer($name);
}, 'timer');
