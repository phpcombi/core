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
class PermMaster implements Interfaces\Perm
{
    use Core\Traits\Singleton;

    public function isAllow(Interfaces\Target $target, $operation = null): bool {
        return $target->ignoreMaster() ? false : $target->hasOperation($operation);
    }
}
