<?php

namespace Combi;

class Tris
{
    public static function du($var, $title = 0): void {
        static $count;
        $count++;
        !$title && $title = "Dump Count: $count";

        $config = combi()->core->config('tris')['dumper'];
        $class  = $config['provider'];
        $class::instance()->dump($var, $title);
    }

    public static function log(string $level, $message, array $context = [],
        $name = 'default'): void {

        static $channels = [];

        if (!isset($channels[$name])) {
            $config = combi()->core->config('tris')['logger'][$name];
            $class  = $config['provider'];
            $channels[$name] = $class::instance($channel, $config);
        }

        $channels[$name]->log($level, $message, $context);
    }
}
