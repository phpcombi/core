<?php

namespace Combi\Meta;

/**
 * Collection和Struct接口实现类赋加迭代器支持
 *
 * @author andares
 */
trait IteratorAggregate {
    /**
     *
     * @return iterable
     */
    public function getIterator() {
        return $this->all();
    }
}
