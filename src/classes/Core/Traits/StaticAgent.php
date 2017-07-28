<?php

namespace Combi\Core\Traits;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};


/**
 * Facade
 *
 *
 * @author andares
 */
trait StaticAgent
{
    public static function __callStatic(string $name, array $arguments) {
        $instance = static::instance();

        return \method_exists($instance, $name)
            ? $instance->$name(...$arguments) : $instance->$name;
    }

    abstract public static function instance();
}
