<?php

namespace Combi\Action;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

/**
 *
 *
 * @author andares
 */
class PermDenied implements Interfaces\Perm
{
    use Core\Traits\Singleton;

    public function isAllow(Interfaces\Target $target, $operation = null): bool {
        return $target->isOpen($operation) ? true : false;
    }
}
