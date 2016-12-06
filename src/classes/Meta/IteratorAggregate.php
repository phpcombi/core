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
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->toArray());
    }
}
