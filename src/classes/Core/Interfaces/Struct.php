<?php

namespace Combi\Core\Interfaces;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 *
 * @author andares
 */
interface Struct extends Arrayable, Confirmable, \Countable,
    \Psr\Container\ContainerInterface
 {
    public static function defaults(bool $includeDeprecated = false): array;

    public static function isKeyDeprecated($key): bool;

    public function set($key, $value);

    public function get($key);

    public function iterate(bool $includeDeprecated = false): iterable;

    public function all(bool $includeDeprecated = false): array;

    public function has($key): bool;

    public function remove($key);

    public function clear();

    public function confirm(bool $includeDeprecated = false);
}
