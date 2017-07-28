<?php

namespace Test;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

core::register(Package::create(__DIR__),
    /*'dependencies', 'hooks',*/ 'helpers');
