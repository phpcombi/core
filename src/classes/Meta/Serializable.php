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
     * 当前版本号
     *
     * @return int|string
     */
    protected static function version() {
        return 1;
    }

    protected static function renew(array $data, $last_version) {
        return $data;
    }

    /**
     * 获取编解码器
     *
     * @return Interfaces\Encoder
     */
    abstract protected static function getEncoder(): Interfaces\Encoder;

    /**
     * 根据数字下标的array填充
     * @param array $arr
     * @param boolean $allow_default
     * @return array
     */
    public function fillByArray(array $arr, $allow_default = false) {
        $count  = 0;
        foreach ($this->defaults() as $name => $default) {
            if ($allow_default && !isset($arr[$count])) {
                $this->$name = $default;
                continue;
            }
            $this->$name = $arr[$count];
            $count++;
        }
    }

    /**
     * 序列化相关
     * @return string
     */
    public function serialize() {
        $arr[] = static::$_version;
        foreach ($this->defaults() as $name => $default) {
            $arr[] = isset($this->$name) ? $this->$name : $default;
        }
        return static::_pack($arr);
    }

    /**
     *
     * @param type $data
     * @throws \UnexpectedValueException
     */
    public function unserialize($data) {
        $arr = static::_unpack($data);
        if (!$arr) {
            throw new \UnexpectedValueException("unpack fail");
        }
        $last_version = array_shift($arr);

        // 触发升级勾子
        if ($last_version != static::$_version) {
            $arr = static::_renew($arr, $last_version);
        }
        if (!$arr) {
            throw new \UnexpectedValueException("unserialize fail");
        }

        $this->fillByArray($arr);
    }

    /**
     * 打包
     *
     * @param array $value
     * @return string
     */
    protected static function _pack(array $value) {
        return static::getEncoder()->encode($value);
    }

    /**
     * 解包
     *
     * @param string $data
     * @return mixed
     */
    protected static function _unpack($data) {
        return static::getEncoder()->decode($data);
    }


}
