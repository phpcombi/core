<?php

namespace Combi\Interfaces;

/**
 *
 * @author andares
 */
interface Struct extends Arrayable {
    public function defaults(bool $include_deprecated = false): array;

    public function isKeyDeprecated($key): bool;

    public function set($key, $value);

    public function get($key);

    public function all(bool $include_deprecated = false);

    public function has($key): bool;

    public function remove($key);

    public function clear();

    public function confirm();
}
