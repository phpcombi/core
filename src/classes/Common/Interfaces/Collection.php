<?php

namespace Combi\Common\Interfaces;

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

    public function append($value);

    public function remove($key);

    public function clear();
}
