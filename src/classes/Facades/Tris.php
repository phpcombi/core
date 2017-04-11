<?php

namespace Combi\Facades;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Tris\Log;


class Tris
{
    /**
     * @var bool
     */
    private static $debug_mode = false;

    /**
     * @var float
     */
    private static $timer = [];

    /**
     * @param mixed $var
     * @param mixed $title
     * @return void
     */
    public static function du($var, $title = null): void {
        static $count, $dumper = null;
        $count++;
        !$title && $title = "Dump Count: $count";

        if (!$dumper) {
            $config = core::config('tris')->dumper;
            $class  = $config['provider'];
            $dumper = $class::instance();
        }
        $dumper->dump($var, $title);
    }

    /**
     * @param mixed $var
     * @param mixed $title
     * @return void
     */
    public static function dd($var, $title = null): void {
        self::du($var, $title);
        die(1);
    }

    public static function debugTurnOn() {
        self::$debug_mode = true;
        self::timer('__debug', true);
    }

    public static function debugTurnOff(): array {
        self::$debug_mode = false;
        return [
            'timecost' => self::timer('__debug'),
        ];
    }

    public static function dt($var, $title = null): void {
        if (self::$debug_mode) {
            ob_start();
            self::du($var, $title);
            $content = ob_get_flush();
            self::log($content);
        }
    }

    public static function timer(string $name, bool $reset = false): float {
        $reset && self::$timer[$name] = null;

        if (self::$timer[$name]) {
            return core::time()->micro() - self::$timer[$name];
        }
        self::$timer[$name] = core::time()->micro();
        return 0;
    }

    /**
     * @param mixed $message
     * @param array $context
     * @param string $level
     * @param string $channel
     */
    public static function log($message, array $context = [],
        string $level = 'debug',
        string $channel = 'default'): void
    {
        static $channels = [];

        if (!isset($channels[$channel])) {
            $config = core::config('tris')->logger[$channel];
            $class  = $config['provider'];
            $channels[$channel] = $class::instance($channel, $config);
        }

        $channels[$channel]->log($level, $message, $context);
    }

    /**
     * @param string|int|abort|\Throwable $message
     * @param array $context
     * @return Log
     */
    public static function ml($message, array $context = []): Log {
        return new Log($message, $context);
    }
}
