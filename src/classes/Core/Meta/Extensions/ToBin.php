<?php

namespace Combi\Core\Meta\Extensions;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 *
 * @author andares
 */
trait ToBin {
    /**
     *
     * @return string
     */
    public function toBin(): string {
        return core\Utils\Pack::encode('msgpack', $this->toArray());
    }
}
