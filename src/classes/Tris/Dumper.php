<?php

namespace Combi\Tris;

use Combi\Traits;

class Dumper implements Interfaces\Dumper
{
    use Traits\Instancable;

    /**
     * @var Providers\Dumper\Base
     */
    private $provider;

    /**
     * @var array
     */
    private $parsed = [];

    public static function du($var, $title = 0): void {
        self::instance()->dump($var, $title);
    }

    public function dump($var, $title = null): self {
        if (combi()->isProd()) {
            return $this;
        }

        if (PHP_SAPI !== 'cli'
            && !preg_match('#^Content-Type: (?!text/html)#im',
                implode("\n", headers_list()))) {

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
