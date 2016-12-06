<?php
namespace Combi\Base;

use Combi\Interfaces;
use Combi\Meta;

/**
 * 标准Struct基类
 *
 * 一个原则：读取单个动态属性时不判断default结构中是否存在，但写入时限制。
 *
 * @author andares
 */
abstract class Struct implements Interfaces\Struct, \IteratorAggregate {
    use Meta\IteratorAggregate;

    /**
     * 基础数据结构
     *
     * @var array
     */
    private $_defaults = [];

    /**
     * 数据
     *
     * @var array
     */
    private $_data = [];

    /**
     * 获取基础数据结构
     * @return array
     */
    public function defaults(): array {
        return $this->_defaults;
    }

    /**
     *
     * @param int|string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value): self {
        isset($this->defaults()[$key]) && $this->_data[$key] = $value;
        return $this;
    }

    /**
     *
     * @param int|string $key
     * @return mixed
     */
    public function get($key) {
        $default = $this->defaults()[$key] ?? null;
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
     * @return self
     * @throws \InvalidArgumentException
     */
    public function confirm(): self {
        foreach ($this->defaults() as $key => $default) {
            $value = $this->get($key);

            $method = "_confirm_$key";
            if (method_exists($this, $method)) {
                $value = $this->$method($value, $default);
            }

            if ($value === null) {
                throw new \InvalidArgumentException(
                    "meta:" . static::class . " field [$key] could not be empty");
            }

            // 展开所有对象进行confirm
            is_object($value) && $value instanceof self && $value->confirm();

            // 赋回
            $this->set($key, $value);
        }

        // 整体confirm勾子
        $this->afterConfirm();
        return $this;
    }

    /**
     * 待扩展的整体confirm方法
     */
    protected function afterConfirm() {}

    /**
     * 将对象展开为一个数组
     *
     * @param callable $filter
     * @return array
     */
    public function toArray(callable $filter = null): array {
        $result = [];
        foreach ($this->defaults() as $key => $default) {
            $value = $this->get($key) ?: $default;

            // 完全展开
            if (is_object($value) && $value instanceof Interfaces\Arrayable) {
                $value = $value->toArray();
            }

            // 过滤器
            $filter && $value = $filter($value);

            // 过滤器支持跳过
            $value !== null && $result[$key] = $value;
        }
        return $result;
    }
}
