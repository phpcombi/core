<?php

namespace Combi\Core\Config\Methods;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class Env extends Core\Config\Method
{
    protected $param;

    public function __invoke() {
        return rt::env($this->getValue());
    }
}