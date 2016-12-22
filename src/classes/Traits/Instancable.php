<?php
namespace Combi\Traits;

/**
 * 提供简单的单件实例化方法。
 * 凡使用该trait的类需要确保不受不同package之间同instance id干扰的影响。
 *
 * @author andares
 */
trait Instancable {
    protected static $_instances = [];

    public static function instance($id = 0, ...$args): self {
        return static::$_instances[$id] ??
            (static::$_instances[$id] = new static($id, ...$args));
    }
}
