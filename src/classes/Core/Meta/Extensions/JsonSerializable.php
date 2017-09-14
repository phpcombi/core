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
trait JsonSerializable {
    /**
     *
     * @return string
     */
    public function __toString(): string {
        return Core\Utils\Pack::encode('json', $this->toArray());
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
