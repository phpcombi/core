<?php

namespace Combi\Meta;

use Combi\Utils\Pack;

/**
 *
 * @author andares
 */
trait JsonSerialize {
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
