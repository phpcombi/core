<?php

namespace Combi\Facades;

class Helper
{
    private static $functions = [];

    public static function __callStatic(string $name, array $arguments) {
        $func = self::$functions[$name];
        return $func(...$arguments);
    }

    public static function register(string $name, callable $func) {
        self::$functions[$name] = $func;
    }
}