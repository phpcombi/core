<?php

if (!function_exists('runtime')) {
    function runtime($id = 0): Combi\Runtime {
        return Combi\Runtime::instance($id);
    }
}
