<?php

namespace Combi\Facades;

class Runtime
{
    public static function __callStatic(string $name, array $arguments) {
        $runtime = \Combi\Core\Runtime::instance();
        return method_exists($runtime, $name)
            ? $runtime->$name(...$arguments) : $runtime->$name;
    }
}