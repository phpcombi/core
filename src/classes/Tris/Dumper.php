<?php

namespace Combi\Tris;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Common\Traits;

class Dumper implements Interfaces\Dumper
{
    use Traits\Singleton;

    /**
     * @var Providers\Dumper\Base
     */
    private $provider;

    /**
     * @var array
     */
    private $parsed = [];

    public function dump($var, $title = null): self {
        if (rt::isProd()) {
            return $this;
        }

        if (is_object($var)) {
            if (method_exists($var, '__debugInfo')) {
                // 啥也不做直接输出
            } elseif (method_exists($var, 'toArray')) {
                $var = $var->toArray();
            } elseif (method_exists($var, '__toString')) {
                $var = (string)$var;
            // } else {
            //     $var = helper::object2array($var);
            }
        // } elseif (is_array($var)) {
        //     $var = helper::object2array($var);
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
