<?php

namespace Combi\Core\Config\Methods;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class Instance extends Core\Config\Method
{
    public function __invoke() {
        if (isset($this->param)) {
            return helper::instance($this->param);
        }
        return helper::instance($this->class, $this->params);
    }
}