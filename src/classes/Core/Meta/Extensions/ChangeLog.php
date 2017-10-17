<?php

namespace Combi\Core\Meta\Extensions;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

/**
 * Collection和Struct接口实现字段变更记录
 *
 * @author andares
 */
trait ChangeLog {
    protected $_originalData = [];
    protected $_isAllChanged = false;

    public function set($key, $value): self {
        if (!$this->_isAllChanged && !array_key_exists($key, $this->_originalData)) {
            $this->_originalData[$key] = $this->get($key);
        }
        return parent::set($key, $value);
    }

    public function remove($key): self {
        if (!$this->_isAllChanged) {
            if (array_key_exists($key, $this->_originalData)) {
                unset($this->_originalData[$key]);
            } else {
                $this->_originalData[$key] = $this->get($key);
            }
        }
        return parent::remove($key);
    }

    public function clear(): self {
        $this->releaseOriginalData();
        $this->_isAllChanged = true;
        return parent::clear();
    }

    public function push($value): self {
        $this->releaseOriginalData();
        $this->_isAllChanged = true;
        return parent::push($value);
    }

    public function getChanges(bool $includeDeprecated = false): array {

        // 如果数据全部为空，或者整体更新
        $data = $this->all($includeDeprecated);
        if (!$data || $this->_isAllChanged) {
            return [null, null, $data];
        }

        $updated = [];
        $removed = [];
        foreach ($this->_originalData as $key => $originalValue) {
            $newValue = $this->get($key);
            if ($newValue == $originalValue) {
                continue;
            }
            if ($newValue === null) {
                $removed[] = $key;
            } else {
                $updated[$key] = $newValue;
            }
        }
        return [$updated, $removed, null];
    }

    public function getOriginalData(bool $includeDeprecated = false): array {
        return $this->_originalData + $this->all($includeDeprecated);
    }

    public function releaseOriginalData(): self {
        $this->_originalData = [];
        $this->_isAllChanged = false;
        return $this;
    }
}
