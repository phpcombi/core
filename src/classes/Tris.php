<?php

namespace Combi;

class Tris
{
    public static function enableCatcher(): void {
        $config = combi()->core->config('tris')['catcher'];
        $class  = $config['provider'];
        $class::instance(0, $config);
    }

    public static function dump($var, $title = 0): void {
        static $count, $dumper = null;
        $count++;
        !$title && $title = "Dump Count: $count";

        if (!$dumper) {
            $config = combi()->core->config('tris')['dumper'];
            $class  = $config['provider'];
            $dumper = $class::instance();
        }
        $dumper->dump($var, $title);
    }

    public static function log(string $level, $message, array $context = [],
        $name = 'default'): void {

        static $channels = [];

        if (!isset($channels[$name])) {
            $config = combi()->core->config('tris')['logger'][$name];
            $class  = $config['provider'];
            $channels[$name] = $class::instance($name, $config);
        }

        $channels[$name]->log($level, $message, $context);
    }
}
