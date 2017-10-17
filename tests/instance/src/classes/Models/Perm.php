<?php

namespace App\Models;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class Perm extends Core\Meta\Struct
    implements \Serializable, \Combi\Action\Interfaces\Perm
{
    use Core\Meta\Extensions\Serializable;

    protected static $_defaults = [
        'id'        => null,
        'targets'   => [],
    ];

    public function isAllow(Interfaces\Target $target, $operation = null): bool {
    }
}
