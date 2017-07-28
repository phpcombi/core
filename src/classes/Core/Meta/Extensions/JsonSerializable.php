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
trait JsonSerializable {
    /**
     *
     * @return string
     */
    public function __toString(): string {
        return core\Utils\Pack::encode('json', $this->toArray());
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
