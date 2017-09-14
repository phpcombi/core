<?php

namespace Combi\Core\Meta\Extensions;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
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
        return Core\Utils\Pack::encode('msgpack', $this->toArray());
    }
}
