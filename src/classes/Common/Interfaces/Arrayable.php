<?php

namespace Combi\Common\Interfaces;

/**
 *
 * @author andares
 */
interface Arrayable {
    public function toArray(callable $filter = null): array;
}
