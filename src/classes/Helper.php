<?php

namespace Combi;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Nette\Neon\Entity;
use Monolog\Formatter\NormalizerFormatter;

class Helper
{
    private static $functions = [];

    public static function __callStatic(string $name, array $arguments) {
        if (isset(core\Logger::LEVELS[$name])) {
            return self::logger()->$name(...$arguments);
        }
        $func = self::$functions[$name];
        return $func(...$arguments);
    }

    /**
     *
     * @param callable $func
     * @param array $names
     * @return void
     */
    public static function register(callable $func, ...$names): void {
        foreach ($names as $name) {
            self::$functions[$name] = $func;
        }
    }

    public static function logger(string $channel = 'combi'): core\Logger {
        return core::logger($channel);
    }

    public static function padding(string $template, array $vars): ?string {
        $result = preg_replace_callback('/(\%)([A-Za-z0-9_\.]+)(\%)|(\%\%)/',
            function($matches) use ($vars) {
                if (isset($matches[4])) {
                    return '%';
                }

                if (isset($vars[$matches[2]])) {
                    return $vars[$matches[2]];
                }
                return $matches[0];
            }, $template);
        return $result;
    }

    public static function invoke(callable $object, ...$arguments) {
        return $object(...$arguments);
    }

    public static function namespace(string $class): string {
        is_object($class) && $class = get_class($class);
        return substr($class, 0, strrpos($class, '\\'));
    }

    public static function make($class, array $params = null) {
        if ($class instanceof Entity) {
            $params = $class->attributes;
            $class  = $class->value;
        }
        return new $class(...$params);
    }

    public static function instance($class, array $params = null) {
        if ($class instanceof Entity) {
            $params = $class->attributes;
            $class  = $class->value;
        }
        return method_exists($class, 'instance')
            ? $class::instance(...$params)
            : new $class(...$params);
    }

    public static function entityWithProcessor(Entity $entity,
        callable $attr_processor): Entity
    {
        $attributes = [];
        foreach ($entity->attributes as $key => $value) {
            $updated = $attr_processor($key, $value);
            $updated !== null && $attributes[] = $updated;
        }
        return new Entity($entity->value, $attributes);
    }

    public static function confirm($object) {
        return $object instanceof core\Interfaces\Confirmable
            ? $object->confirm() : $object;
    }

    public static function stringify($var): string {
        return str_replace(["\r\n", "\r", "\n"], ' ',
            json_encode((new NormalizerFormatter())->format($var),
                \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE));
    }
}