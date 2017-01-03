<?php
namespace {
    /**
     * @return Combi\Core\Runtime
     */
    function combi(): Combi\Core\Runtime {
        return Combi\Core\Runtime::instance();
    }
}

namespace combi {
    /**
     * @param string $class
     * @return string
     */
    function get_namespace(string $class): string {
        is_object($class) && $class = get_class($class);
        return substr($class, 0, strrpos($class, '\\'));
    }
}
