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
class TargetWhatever implements Interfaces\Target
{
    use Core\Traits\Singleton;

    public $targetId    = '';
    public $isOpen      = true;

    public function getTargetId(): string {
        return $this->targetId;
    }

    public function hasOperation($operation): bool {
        return true;
    }

    public function ownedOperations() {
        return new class implements \ArrayAccess, \IteratorAggregate {
            use Core\Traits\ArrayDummy;
        };
    }

    public function isOpen($operation): bool {
        return $this->isOpen;
    }

    public function ignoreMaster(): bool {
        return false;
    }
}
