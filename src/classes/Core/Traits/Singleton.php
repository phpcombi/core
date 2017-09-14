<?php

namespace Combi\Core\Traits;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 * 单件实例化方法。
 *
 * @author andares
 */
trait Singleton {
    public static function instance(...$args): self {
        return rt::$_singletons[static::class] ??
            (rt::$_singletons[static::class] = new static(...$args));
    }

    public static function reinstance(...$args): self {
        unset(r::$_singletons[static::class]);
        return static::instance(...$args);
    }
}
