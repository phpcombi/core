<?php

namespace App;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

rt::register(Package::instance(__DIR__),
    /*'dependencies', 'hooks',*/ 'helpers');
