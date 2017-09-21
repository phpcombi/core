<?php

namespace Combi\Core\Config\Methods;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class Make extends Core\Config\Method
{
    public function __invoke() {
        if ($val = $this->getValue()) {
            return helper::make($val);
        }
        return helper::make($this->class, $this->params);
    }
}