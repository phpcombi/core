<?php

namespace Combi\Core\Traits;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 *
 * @author andares
 */
trait GetNamespace {
    public static function namespace(): string {
        return helper::namespace(static::class);
    }
}
