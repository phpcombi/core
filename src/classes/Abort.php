<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Runtime as rt
};

class Abort
{
    use Core\Traits\With;

    protected static function pretreat(string $name, array $arguments): array {
        return [helper::make(ucfirst($name).'Exception', $arguments)];
    }

    public static function with(\Throwable $e, callable $maker = null,
        ...$arguments): Core\Abort
    {
        $abort = new Core\Abort($e);
        return $maker ? $maker($abort, ...$agruments) : $abort;
    }
}
