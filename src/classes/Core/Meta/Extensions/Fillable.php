<?php

namespace Combi\Core\Meta\Extensions;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};


/**
 * Collection和Struct接口实现类赋加fill相关接口支持
 *
 * @author andares
 */
trait Fillable {

    /**
     * 填充时排除字段
     *
     * @var array
     */
    private $_exclude_keys = [];

    /**
     * 填充数据方法
     *
     * @param \Traversable|array $data
     * @return self
     * @throws \InvalidArgumentException
     */
    public function fill($data): self {
        if (!is_array($data) && !is_object($data) && $data instanceof \Traversable) {
            throw new \InvalidArgumentException("fill data error");
        }

        // 兼容struct与collection
        foreach ($this instanceof coreInterfaces\Struct ? static::defaults() : $data
            as $key => $value) {

            if (isset($this->_exclude_keys[$key])) {
                continue;
            }
            isset($data[$key]) && $this->set($key, $data[$key]);
        }
        return $this;
    }

    /**
     * 添加要排除的字段。不传参数为添空之前添加的排除字段。
     *
     * @param array $keys
     * @return self
     */
    public function exclude(...$keys): self {
        if ($keys) {
            foreach ($keys as $key) {
                $this->_exclude_keys[$key] = 1;
            }
        } else {
            $this->_exclude_keys = [];
        }
        return $this;
    }

}
