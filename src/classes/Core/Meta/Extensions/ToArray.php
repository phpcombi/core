<?php

namespace Combi\Core\Meta\Extensions;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 *
 * @author andares
 */
trait ToArray {
    /**
     * 将对象展开为一个数组
     *
     * @param callable $filter
     * @return array
     */
    public function toArray(callable $filter = null): array {
        $result = [];
        foreach ($this->iterate() as $key => $value) {
            // 完全展开
            if (is_object($value) && $value instanceof core\Interfaces\Arrayable) {
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
