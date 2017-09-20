<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

helper::register(function_exists('gmp_init')
    ? function(int $random_length = 6): string
{
    return (new Core\Utils\IdGenerator())
        ->randByLength($random_length)
        ->orderable()
        ->gmp_strval()
        ->get();
}
    : function(int $random_length = 6): string
{
    return (new Core\Utils\IdGenerator())
        ->randByLength($random_length)
        ->orderable()
        ->to62()
        ->get();
}, 'genId');


helper::register(function($var, $title = null): void {
    static $count;
    $count++;
    !$title && $title = "Dump Count: $count";

    Core\Utils\Debug::instance()->dump($var, $title);
}, 'du', 'dt');

helper::register(function($var, $title = null): void {
    helper::du($var, $title);
    die(1);
}, 'dd');

helper::register(function($message, array $context = []): void {
    helper::logger()->info($message, $context);
}, 'log');

helper::register(function($message, array $context = []): void {
    helper::logger('debug')->debug($message, $context);
}, 'debug');

helper::register(function(string $name = 'default'): float {
    return Core\Utils\Debug::instance()->timer($name);
}, 'timer');
