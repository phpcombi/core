<?php

namespace Combi\Core\Config\Methods;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

use Nette\Neon\Entity;

class Constant extends Core\Config\Method
{
    protected $param;

    public function __invoke() {
        if ($this->param instanceof Entity) {
            $value = constant($this->param->value);
            return $value[$this->param->attributes[0]];
        }
        return constant($this->param);
    }
}