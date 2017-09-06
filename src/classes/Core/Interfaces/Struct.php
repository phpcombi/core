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
interface Struct extends Arrayable, Confirmable,
    \Psr\Container\ContainerInterface
 {
    public static function defaults(bool $include_deprecated = false): array;

    public static function isKeyDeprecated($key): bool;

    public function set($key, $value);

    public function get($key);

    public function iterate(bool $include_deprecated = false): iterable;

    public function all(bool $include_deprecated = false): array;

    public function has($key): bool;

    public function remove($key);

    public function clear();

    public function confirm(bool $include_deprecated = false);
}
