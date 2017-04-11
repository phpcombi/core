<?php

namespace Combi\Meta\Extensions;

use Combi\Utils\Pack;

/**
 *
 * @author andares
 */
trait JsonSerializable {
    /**
     *
     * @return string
     */
    public function __toString(): string {
        return Pack::encode('json', $this->toArray());
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
