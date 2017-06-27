<?php

namespace Test;

use Combi\Facades\Runtime as rt;

rt::register(Package::instance(__DIR__),
    /*'dependencies', 'hooks',*/ 'helpers', 'business');
