<?php

namespace Combi\Core\Traits;

/**
 *
 * @author andares
 */
trait Instancable {
    protected static $_instances = [];

    public static function instance($id = 0, ...$args): self {
        $instance = static::$_instances[$id] ??
            (static::$_instances[$id] = new static($id, ...$args));
        return $instance;
    }
}
