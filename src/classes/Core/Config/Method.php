<?php

namespace Combi\Core\Config;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

abstract class Method
{
    abstract public function __invoke();

    public function __construct($params) {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                ($value || $value == false) && $this->$key = $value;
            }
        } else {
            $this->param = $params;
        }
    }

    public static function __set_state(array $params) {
        $method = new static($params);
        return $method();
    }
}