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
        $val = $this->getValue();
        if ($val instanceof Entity) {
            $const = constant($val->value);
            return $const[$val->attributes[0]];
        }
        return constant($val);
    }
}