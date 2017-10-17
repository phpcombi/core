<?php

namespace Combi\Action\Interfaces;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 *
 *
 * @author andares
 */
interface Perm
{
    public function isAllow(Target $target, $operation = null): bool;
}
