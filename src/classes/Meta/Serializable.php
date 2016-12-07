<?php
namespace Combi\Meta;

use Combi\Interfaces;

/**
 * （仅为）Struct接口实现类赋加重载访问属性支持
 *
 * @author andares
 */
trait Serializable {
    /**
     * 待扩展的解包后复苏方法
     */
    protected function afterUnpack() {}

    /**
     * 获取编解码器
     *
     * @return Interfaces\Encoder
     */
    abstract protected static function getEncoder(): Interfaces\Encoder;

    /**
     * 当前版本号。
     * 因为加入了deprecated机制，在不减少或变更字段顺序，并且字段类型一致的情况下可尽量减少version和renew的使用
     *
     * @return int|string
     */
    protected static function version() {
        return 1;
    }

    /**
     * 结合version时用的扩展方法，通过增加迁移代码实现解包时的数据一致性维护。
     *
     * @param array $data
     * @param int|string $last_version
     * @return array
     */
    protected static function renew(array $data, $last_version) {
        return $data;
    }

    /**
     * 根据数字下标的array填充
     * @param array $arr
     * @return self
     */
    public function fillByArray(array $arr): self {
        $count  = 0;
        foreach (static::defaults(true) as $key => $default) {
            isset($arr[$count]) && $this->set($key, $arr[$count]);
            $count++;
        }
        return $this;
    }

    /**
     * 序列化相关
     * @return string
     */
    public function serialize(): ?string {
        $arr['#'] = static::version();
        $count = -1;
        foreach ($this->all(true) as $key => $value) {
            $count++;
            // 跳过弃用
            if (static::isKeyDeprecated($key)) {
                continue;
            }
            $arr[$count] = $value;
        }
        return static::_pack($arr);
    }

    /**
     *
     * @param string $data
     * @throws \UnexpectedValueException
     */
    public function unserialize($data): void {
        $arr = static::_unpack($data);
        if (!$arr) {
            throw new \UnexpectedValueException("unpack fail");
        }
        $last_version = $arr['#'];
        unset($arr['#']);

        // 触发升级勾子
        if ($last_version != static::version()) {
            $arr = static::renew($arr, $last_version);
        }
        if (!$arr) {
            throw new \UnexpectedValueException("unserialize fail");
        }

        $this->fillByArray($arr)->afterUnpack();
    }

    /**
     * 打包
     *
     * @param array $value
     * @return string
     */
    protected static function _pack(array $value): ?string {
        return static::getEncoder()->encode($value);
    }

    /**
     * 解包
     *
     * @param string $data
     * @return mixed
     */
    protected static function _unpack(string $data) {
        return static::getEncoder()->decode($data);
    }


}
