<?php
namespace {
    /**
     * @return Combi\Core\Runtime
     */
    function combi(): Combi\Core\Runtime {
        return Combi\Core\Runtime::instance();
    }

    /**
     * 该方法会与laravel产生冲突。
     * 在laravel中使用时，combi/core的载入优先级需要高于laravel；
     * 如果对laravel中该函数有使用，需要另行处理。
     *
     * @param \Throwable $e
     * @param callable $maker
     * @param array $arguments
     * @return Combi\Abort
     */
    function abort(\Throwable $e,
        callable $maker = null, ...$arguments): Combi\Abort {

        $abort = new Combi\Abort($e);
        return $maker ? $maker($abort, ...$arguments) : $abort;
    }

    if (!function_exists('du')) {
        function du($var, $title = 0): void {
            Combi\Tris::du($var, $title);
        }
    }

}

namespace combi {

    /**
     * @param string $template
     * @param array $vars
     * @return string
     */
    function padding(string $template, array $vars): string {
        $result = preg_replace_callback('/:([A-Za-z0-9_\.]+) |\{([A-Za-z0-9_\.]+)\}/',
            function($matches) use ($vars) {
                $key = $matches[2] ?? $matches[1];
                return $vars[$key] ?? $matches[0];
            }, $template);
        return $result;
    }

    /**
     * @param string $class
     * @return string
     */
    function get_namespace(string $class): string {
        is_object($class) && $class = get_class($class);
        return substr($class, 0, strrpos($class, '\\'));
    }
}
