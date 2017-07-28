<?php

namespace Combi\Core\Traits;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 * 单件实例化方法。
 *
 * @author andares
 */
trait Singleton {
    public static function instance(...$args): self {
        return core::$singletons[static::class] ??
            (core::$singletons[static::class] = new static(...$args));
    }

    public static function reinstance(...$args): self {
        unset(core::$singletons[static::class]);
        return static::instance(...$args);
    }
}
