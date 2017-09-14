<?php

namespace App;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

class Package extends \Combi\Package
{
    protected static $_pid = 'app';
}
