<?php
namespace Combi\Traits;

/**
 *
 * @author andares
 */
trait GetNamespace {
    public static function namespace(): string {
        return \combi\get_namespace(static::class);
    }
}
