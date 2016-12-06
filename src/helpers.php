<?php

namespace {
    if (!function_exists('runtime')) {
        function runtime(): Combi\Core\Runtime {
            return Combi\Core\Runtime::instance();
        }
    }

    if (!function_exists('rt')) {
        function rt(): Combi\Core\Runtime {
            return Combi\Core\Runtime::instance();
        }
    }
}

namespace combi {
    function runtime(): Combi\Core\Runtime {
        return Combi\Core\Runtime::instance();
    }

    function rt(): Combi\Core\Runtime {
        return Combi\Core\Runtime::instance();
    }
}

