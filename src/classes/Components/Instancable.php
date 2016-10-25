<?php
namespace Combi\Core\Components;

/**
 * Description of Instancable
 *
 * @author andares
 */
trait Instancable {
    protected static $_instances = [];

    public static function instance($id = 0): self {
        return self::$_instances[$id] ?? (self::$_instances[$id] = new self());
    }
}
