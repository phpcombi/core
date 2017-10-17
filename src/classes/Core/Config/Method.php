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
    protected $_value = null;

    abstract public function __invoke();

    public function __construct($params) {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                ($value || $value == false) && $this->$key = $value;
            }
        } else {
            $this->_value = $params;
        }
    }

    protected function getValue() {
        return $this->_value;
    }

    public static function __set_state(array $params) {
        $method = new static($params);
        return $method();
    }
}