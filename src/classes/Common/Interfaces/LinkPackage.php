<?php

namespace Combi\Common\Interfaces;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Core\Package;

/**
 *
 * @author andares
 */
interface LinkPackage {
    public function linkPackage(Package $package, string $name);
    public function innerPackage(): Package;
}
