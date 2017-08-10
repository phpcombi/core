<?php

namespace App;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

core::register(Package::instance(__DIR__),
    /*'dependencies', 'hooks',*/ 'helpers');
