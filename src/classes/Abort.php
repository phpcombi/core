<?php

namespace Combi;

use Combi\Base;
use Combi\Meta;

/**
 * Description of Abort
 *
 * @author andares
 */
class Abort extends \Exception {
    use Meta\Overloaded;

    /**
     * 数据
     * @var array
     */
    protected $_data = [];

    public function __construct(\Throwable $e) {
        parent::__construct($e->getMessage(), $e->getCode(), $e);
    }

    public function __invoke(): \Throwable {
        return $this->getPrevious();
    }

	public function __toString(): string {
        $e = $this->getPrevious();
        return "$e";
    }

    public function class(): string {
        return get_class($this->getPrevious());
    }

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
     *
     * @param int|string $key
     * @return bool
     */
    public function has($key): bool {
        return isset($this->_data[$key]);
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
     * 返回一个遍历内部数据的迭代器
     *
     * @return iterable
     */
    public function all(): iterable {
        foreach ($this->_data as $key => $value) {
            yield $key => $value;
        }
    }
}
