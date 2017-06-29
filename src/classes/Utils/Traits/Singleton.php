<?php

namespace Combi\Utils\Traits;

use Combi\Core\Runtime;

/**
 * 单件实例化方法。
 *
 * @author andares
 */
trait Singleton {
    public static function instance(...$args): self {
        return Runtime::$_singletons[static::class] ??
            (Runtime::$_singletons[static::class] = new static(...$args));
    }
}
