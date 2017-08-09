<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Combi\Utils\ArrayCover;
use Nette\Neon\Neon;

/**
 * Description of Config
 *
 * @author andares
 */
class Config extends core\Meta\Container implements \ArrayAccess
{
    use core\Meta\Extensions\Overloaded,
        core\Meta\Extensions\ArrayAccess;

    protected $_name;

    protected $_scene;

    /**
     * @var Resource\Directory
     */
    protected $_source  = null;

    /**
     * @var Resource\Directory
     */
    protected $_cache   = null;

    /**
     *
     * @param string $name
     * @param Resource\Directory|null $source
     * @param string $scene
     * @param Resource\Directory|null $cache
     */
    public function __construct(string $name,
        ?Resource\Directory $source = null, string $scene = '',
        ?Resource\Directory $cache  = null) {

        $this->_name    = $name;
        $this->_scene   = $scene;

        $source && $this->_source   = $source;
        $cache  && $this->_cache    = $cache;

        // 未设置源时不载入 这是为了方便测试
        if ($this->_source) {
            $this->_data = $this->load();
        }
    }

    /**
     * @return array
     */
    public function raw(): array {
        return $this->_data;
    }

    /**
     *
     * @return array
     */
    protected function load(): array {
        // 配置文件路径
        $source_file = $this->_source->select("$this->_name.neon");
        if (!file_exists($source_file)) {
            return [];
        }

        // 检查缓存
        if ($this->_cache) {
            // 缓存文件名
            $cache_file = $this->_cache->select("$this->_name.$this->_scene.php");

            // 从缓存中读取
            $data = $this->loadByCache($cache_file, $source_file);
            if (!$data) {
                $data = $this->parse($source_file);

                // 回写入缓存
                $this->_cache->write("$this->_name.$this->_scene.php", '<?php
return '.var_export($data, true).';');
            }
        } else {
            $data = $this->parse($source_file);
        }

        return $data;
    }

    /**
     *
     * @param string $cache_file
     * @return array
     */
    protected function loadByCache(string $cache_file, string $source_file): array {
        $data = [];

        if (core::isProd()) {
            // 生产环境直接载入
            $data = @include $cache_file;
        } else {
            // 非生产环境检查更新情况
            if (file_exists($cache_file)) {
                @clearstatcache(false, $source_file);
                @clearstatcache(false, $cache_file);
                if (filemtime($source_file) <= filemtime($cache_file)) {
                    $data = @include $cache_file;
                }
            }
        }

        return $data;
    }

    /**
     *
     * @param string $source_file
     * @return array
     */
    protected function parse(string $source_file): array {
        $raw = Neon::decode(file_get_contents($source_file));
        $raw && $this->prune($raw);
        return $raw;
    }

    /**
     *
     * @param array $parent
     * @return void
     */
    protected function prune(array &$parent): void {
        foreach ($parent as $key => $child) {
            if (strpos($key, ' < ')) {
                if (!$this->_scene) { // 未设置场景时只用默认值
                    unset($parent[$key]);
                    continue;
                }

                [$scene, $skey] = explode(' < ', $key);
                if ($scene == $this->_scene) {
                    $parent[$skey] = $parent[$key];
                }
                unset($parent[$key]);
            }
        }

        foreach ($parent as $key => $child) {
            if (is_array($child)) {
                $this->prune($parent[$key]);
            }
        }
    }

    /**
     * 替换集合中已经存在的键值。
     * 这里可支持数组所有深度的覆盖合并。
     *
     * @param array $items
     * @return self
     */
    public function replace(array $items): self {
        $ac = new ArrayCover($this->_data);
        $this->_data = $ac($items);
        return $this;
    }

}
