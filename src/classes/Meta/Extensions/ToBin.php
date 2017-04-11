<?php

namespace Combi\Meta\Extensions;

use Combi\Utils\Pack;

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
        return Pack::encode('msgpack', $this->toArray());
    }
}
