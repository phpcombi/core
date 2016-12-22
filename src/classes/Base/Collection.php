<?php

namespace Combi\Base;

use Combi\Interfaces;
use Combi\Meta;

/**
 * 标准Collection基类
 *
 * @author andares
 */
abstract class Collection implements Interfaces\Collection, \IteratorAggregate {
    use Meta\IteratorAggregate, Meta\ToArray;

    /**
     * 数据
     * @var array
     */
    protected $_data = [];

    /**
     *
     * @param int|string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value): self {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     *
     * @param int|string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->_data[$key] ?? $default;
    }

    /**
     * 替换集合中已经存在的键值
     *
     * @param array $items
     * @return self
     */
    public function replace(array $items): self {
        foreach ($items as $key => $value) {
            isset($this->_data[$key]) && $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * 返回一个遍历内部数据的迭代器
     *
     * @return iterable
     */
    public function all(): iterable {
        foreach ($this->_data as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     *
     * @param int|string $key
     * @return bool
     */
    public function has($key): bool {
        return isset($this->_data[$key]);
    }

    /**
     * 向集合中以自增键添加一个单元
     *
     * @param mixed $value
     * @return self
     */
    public function append($value): self {
        $this->_data[] = $value;
        return $this;
    }

    /**
     * 移除一个单元
     *
     * @param int|string $key
     * @return self
     */
    public function remove($key): self {
        unset($this->_data[$key]);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function clear(): self {
        $this->_data = [];
        return $this;
    }

    /**
     *
     * @return int
     */
    public function count(): int {
        return count($this->_data);
    }
}
