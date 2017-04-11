<?php

namespace Combi\Facades;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

class Package
{
    protected static $pid = '';

    public static function __callStatic(string $name, array $arguments) {
        $instance = \Combi\Core\Package::instance(static::$pid);

        return $instance->has($name)
            ? $instance->$name : $instance->$name(...$arguments);
    }

    public static function instance(string $src_path = null) {

        return $src_path
            ? \Combi\Core\Package::instance(static::$pid, $src_path)
            : \Combi\Core\Package::instance(static::$pid);
    }
}