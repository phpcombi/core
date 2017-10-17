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
interface Target
{
    public function getTargetId();
    public function hasOperation($operation): bool;
    public function ownedOperations();
    public function isOpen($operation): bool;
    public function ignoreMaster(): bool;
}
