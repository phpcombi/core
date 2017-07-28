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
trait With
{
    public static function __callStatic(string $name, array $arguments) {
        return static::with(...static::pretreat($name, $arguments));
    }

    abstract protected static function pretreat(string $name,
        array $agruments): array;

    abstract public static function with();
}
