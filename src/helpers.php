<?php

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

function combi(): Combi\Core\Runtime {
    return Combi\Core\Runtime::instance();
}

helper::register('padding', function(string $template, array $vars): ?string {
    $result = preg_replace_callback(
        '/(\s?):([A-Za-z0-9_\.]+)(\s?)|(\{?)(\{)([A-Za-z0-9_\.]+)(\})(\}?)/',

        function($matches) use ($vars) {
            $key = $matches[6] ?? $matches[2];
            if (isset($matches[8]) && $matches[4] == '{' && $matches[8] == '}') {
                return $matches[5].$matches[6].$matches[7];
            } elseif (isset($vars[$key])) {
                return isset($matches[8])
                    ? ($matches[4].$vars[$key].$matches[8])
                    : ($matches[1].$vars[$key].$matches[3]);
            } else {
                return $matches[0];
            }
        }, $template);
    return $result;
});

helper::register('namespace', function(string $class): string {
    is_object($class) && $class = get_class($class);
    return substr($class, 0, strrpos($class, '\\'));
});
