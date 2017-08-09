<?php

namespace Combi\Core\Interfaces;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 *
 * @author andares
 */
interface Collection extends Arrayable, \Countable {
    public function set($key, $value);

    public function get($key);

    public function replace(array $items);

    public function iterate(): iterable;

    public function all(): array;

    public function has($key): bool;

    public function push($value);

    public function remove($key);

    public function clear();
}
