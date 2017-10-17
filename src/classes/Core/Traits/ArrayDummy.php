<?php

namespace Combi\Core\Traits;

/**
 *
 * @author andares
 */
trait ArrayDummy {
    protected $_dummyData = [];

    public function offsetSet($offset, $value) {
        $this->_dummyData[$offset] = $value;
    }

    public function offsetGet($offset) {
        return $this->_dummyData ? ($this->_dummyData[$offset] ?? null) : true;
    }

    public function offsetExists($offset): bool {
        return $this->_dummyData ? isset($this->_dummyData[$offset]) : true;
    }

    public function offsetUnset($offset): void {
        unset($this->_dummyData[$offset]);
    }

    public function data($data): self {
        $this->_dummyData = $data;
        return $this;
    }

    /**
     *
     * @return iterable
     */
    public function getIterator() {
        return $this->_dummyData;
    }
}
