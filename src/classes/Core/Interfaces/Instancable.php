<?php

namespace Combi\Core\Interfaces;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 *
 * @author andares
 */
interface Instancable {
    public static function instance();
}
