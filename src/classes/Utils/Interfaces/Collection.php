<?php

namespace Combi\Utils\Interfaces;

/**
 *
 * @author andares
 */
interface Collection extends Arrayable, \Countable {
    public function set($key, $value);

    public function get($key, $default = null);

    public function replace(array $items);

    public function iterate(): iterable;

    public function all(): array;

    public function has($key): bool;

    public function push($value);

    public function remove($key);

    public function clear();
}
