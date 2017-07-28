<?php

namespace Combi\Core\Utils;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

class Debug
{
    use core\Traits\Singleton;

    /**
     * @var array
     */
    private $timer = [];

    public function timer(string $name = 'default'): float
    {
        if (!isset($this->timer[$name])) {
            $this->timer[$name] = core::time()->micro();
            return 0;
        } else {
            $duration = core::time()->micro() - $this->timer[$name];
            unset($this->timer[$name]);
            return $duration;
        }
    }

    public function dump($var, $title = null): self {
        if (core::isProd()) {
            return $this;
        }

        if (is_object($var)) {
            if (method_exists($var, 'toArray')) {
                $var = $var->toArray();
            } elseif (method_exists($var, '__toString')) {
                $var = (string)$var;
            }
        }

        if (PHP_SAPI !== 'cli'
            && !preg_match('#^Content-Type: (?!text/html)#im',
                implode("\n", headers_list()))
            && !defined('TESTING')) {

            // html
            if ($title) {
                echo "<h3># $title</h3>";
            }
            echo "<pre>";
            var_dump($var);
            echo "</pre>";
        } elseif((getenv('ConEmuANSI') === 'ON'
            || getenv('ANSICON') !== FALSE
            || getenv('term') === 'xterm-256color'
            || (defined('STDOUT')
                && function_exists('posix_isatty')
                && posix_isatty(STDOUT)))) {

            // terminal
            if ($title) {
                echo "\n\033[44;37;5m### $title\033[0m\n";
            }
            var_dump($var);
        } else {
            // plain
            if ($title) {
                echo "\n### $title\n";
            }
            var_dump($var);
        }
        return $this;
    }
}
