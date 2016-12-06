<?php

namespace Combi\Utils;

use Combi\Interfaces;

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
     * @return mixed
     */
    public static function encode(string $format, $value) {
        return self::getEncoder($format)->encode($value);
    }

    /**
     *
     * @param string $format
     * @param mixed $data
     * @return mixed
     */
    public static function decode(string $format, $data) {
        return self::getEncoder($format)->decode($data);
    }

    /**
     *
     * @param string $name
     * @return Interfaces\Encoder
     */
    public static function getEncoder(string $name): Interfaces\Encoder {
        if (!isset(static::$encoders[$name])) {
            $class = static::class . '\\' . ucfirst($name);
            static::$encoders[$name] = new $class();
        }
        return static::$encoders[$name];
    }
}
