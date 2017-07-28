<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

class Package
{
    use core\Traits\StaticAgent;

    protected static $pid = null;

    public static function create(string $src_path): Core\Package {
        return Core\Package::instance(static::$pid, $src_path);
    }

    public static function instance(): Core\Package {
        return Core\Package::instance(static::$pid);
    }
}
