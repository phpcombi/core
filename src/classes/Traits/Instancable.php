<?php
namespace Combi\Traits;

/**
 * Description of Instancable
 *
 * @author andares
 */
trait Instancable {
    protected static $_instances = [];

    public static function instance($id = 0, ...$args): self {
        return static::$_instances[$id] ??
            (static::$_instances[$id] = new static(...$args));
    }
}
