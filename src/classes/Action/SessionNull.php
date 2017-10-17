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
class SessionNull extends Core\Meta\Collection implements Interfaces\Session
{
    use Core\Traits\Singleton,
        Core\Meta\Extensions\Overloaded;

    public function load() {}
    public function save() {}
    public function setExpire(int $expire): self {
        return $this;
    }
}
