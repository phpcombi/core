<?php

namespace Combi\Utils\Traits;

/**
 * 实例控制
 *
 * @author andares
 */
trait InstanceControl {
    protected $_sub_instance = [];

    abstract protected function createSubInstance($name);

    public function getSubInstance($name) {
        if (!isset($this->_sub_instance[$name])) {
            $this->_sub_instance[$name] = $this->createSubInstance($name);
        }
        return $this->_sub_instance[$name];
    }
}
