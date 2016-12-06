<?php

namespace Combi\Interfaces;

/**
 *
 * @author andares
 */
interface Arrayable {
    public function toArray(callable $filter = null): array;
}
