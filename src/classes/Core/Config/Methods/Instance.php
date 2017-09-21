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
        if ($val = $this->getValue()) {
            return helper::instance($val);
        }
        return helper::instance($this->class, $this->params);
    }
}