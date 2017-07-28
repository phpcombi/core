<?php

namespace Combi\Core\Utils;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 *
 * @author andares
 */
class Pack {
    /**
     *
     * @var array
     */
    protected static $encoders = [];

    /**
     *
     * @param string $format
     * @param mixed $value
     * @return string
     */
    public static function encode(string $format, $value): ?string {
        return self::getEncoder($format)->encode($value);
    }

    /**
     *
     * @param string $format
     * @param mixed $data
     * @return mixed
     */
    public static function decode(string $format, string $data) {
        return self::getEncoder($format)->decode($data);
    }

    /**
     *
     * @param string $name
     * @return core\Interfaces\Encoder
     */
    public static function getEncoder(string $name): core\Interfaces\Encoder {
        if (!isset(static::$encoders[$name])) {
            $class = static::class . '\\' . ucfirst($name);
            static::$encoders[$name] = new $class();
        }
        return static::$encoders[$name];
    }
}
