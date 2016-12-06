<?php

namespace Combi\Interfaces;

/**
 * Collection Interface
 *
 * @package Slim
 * @since   3.0.0
 */
interface Collection extends Arrayable, \Countable
{
    public function set($key, $value);

    public function get($key, $default = null);

    public function replace(array $items);

    public function all();

    public function has($key): bool;

    public function append($value);

    public function remove($key);

    public function clear();
}
