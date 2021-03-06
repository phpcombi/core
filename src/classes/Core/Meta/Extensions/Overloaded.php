<?php

namespace Combi\Core\Meta\Extensions;

/**
 * Collection和Struct接口实现类赋加重载访问属性支持
 *
 * @author andares
 */
trait Overloaded {
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    public function __get($key) {
        return $this->get($key);
    }

    public function __isset($key) {
        return $this->has($key);
    }

    public function __unset($key) {
        $this->remove($key);
    }
}
