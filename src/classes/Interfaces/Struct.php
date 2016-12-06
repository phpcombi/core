<?php

namespace Combi\Interfaces;

/**
 *
 * @author andares
 */
interface Struct extends Arrayable {
    public function defaults(): array;

    public function set($key, $value);

    public function get($key, $default = null);

    public function has($key): bool;

    public function remove($key);

    public function clear();

    public function confirm();
}
