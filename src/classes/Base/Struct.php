<?php
namespace Combi\Base;

use Combi\Interfaces;
use Combi\Meta;

/**
 * 标准Struct基类
 *
 * 一个原则：读取单个动态属性时不判断default结构中是否存在，但写入时限制。
 *
 * deprecated的策略是当单个存取的时候不受影响，但批量读取、处理、填充时将默认跳过。
 *
 * @author andares
 */
abstract class Struct
    implements Interfaces\Struct, \IteratorAggregate, \JsonSerializable
{
    use Meta\IteratorAggregate, Meta\ToArray, Meta\JsonSerializable;

    /**
     * 基础数据结构
     *
     * @var array
     */
    protected static $_defaults = [];

    /**
     * 已弃用字段
     *
     * @var array
     */
    protected static $_deprecated = [];

    /**
     * 数据
     *
     * @var array
     */
    protected $_data = [];

    /**
     * 返回当前拥有数据（而非定义）的 keys 列表
     *
     * @return array
     */
    public function keys(): array {
        return array_keys($this->_data);
    }

    /**
     * 获取基础数据结构
     * @return array
     */
    public static function defaults(bool $include_deprecated = false): array {
        static $enabled = [];
        if ($include_deprecated) {
            return static::$_defaults;
        }

        if (!$enabled) {
            foreach (static::$_defaults as $key => $default) {
                !static::isKeyDeprecated($key) && $enabled[$key] = $default;
            }
        }
        return $enabled;
    }

    /**
     * 是否为已弃用字段
     *
     * @param int|string $key
     * @return bool
     */
    public static function isKeyDeprecated($key): bool {
        return isset(static::$_deprecated[$key]);
    }

    /**
     *
     * @param int|string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value): self {
        array_key_exists($key, static::defaults(true)) &&
            $this->_data[$key] = $value;
        return $this;
    }

    /**
     *
     * @param int|string $key
     * @return mixed
     */
    public function get($key) {
        return $this->_data[$key] ?? (static::defaults(true)[$key] ?? null);
    }

    /**
     * 返回一个遍历内部数据的迭代器
     *
     * @param bool $include_deprecated
     * @return iterable
     */
    public function iterate(bool $include_deprecated = false): iterable {
        foreach (static::defaults($include_deprecated) as $key => $default) {
            yield $key => $this->_data[$key] ?? $default;
        }
    }

    /**
     * 返回全部属性
     *
     * @param bool $include_deprecated
     * @return array
     */
    public function all(bool $include_deprecated = false): array {
        $result = [];
        foreach ($this->iterate($include_deprecated) as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
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
     * @param bool $include_deprecated
     * @return self
     * @throws \UnexpectedValueException
     */
    public function confirm(bool $include_deprecated = false): self {
        foreach ($this->iterate($include_deprecated) as $key => $value) {
            $method = "_confirm_$key";
            if (method_exists($this, $method)) {
                $value = $this->$method($value);
            } elseif ($value === null) {
                throw new \UnexpectedValueException(
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
    protected function afterConfirm(): void {}

}
