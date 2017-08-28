<?php

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

class App
{
    use core\Traits\StaticAgent;

    public static function instance(): App\Package {
        return App\Package::instance();
    }

}
