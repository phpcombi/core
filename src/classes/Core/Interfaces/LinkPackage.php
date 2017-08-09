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
interface LinkPackage {
    public function linkPackage(core\Package $package, string $name);
    public function innerPackage(): core\Package;
}