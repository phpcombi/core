<?php

namespace Combi\Core;

use Combi\Base\Container;
use Combi\Meta;
use Combi\Utils\ArrayCover;
use Nette\Neon\{Neon, Entity};

/**
 * Description of Config
 *
 * @author andares
 */
class Config extends Container
{
    use Meta\Overloaded;

    protected $_name;

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
     * @param ?Resource\Directory $source
     * @param ?Resource\Directory $cache
     */
    public function __construct(string $name,
        ?Resource\Directory $source = null,
        ?Resource\Directory $cache  = null) {

        $this->_name = $name;

        $source && $this->_source   = $source;
        $cache  && $this->_cache    = $cache;

        // 未设置源时不载入
        if ($this->_source) {
            $this->_data = $this->load();
        }
    }

    /**
     *
     * @return array
     */
    protected function load(): array {
        // 场景获取
        $scene = combi()->config('scene');
        // 配置文件路径
        $source_file = $this->_source->select("$this->_name.neon");

        // 检查缓存
        if ($this->_cache) {
            // 缓存文件名
            $cache_file = $this->_cache->select("$this->_name.$scene.php");

            // 从缓存中读取
            $data = $this->loadByCache($cache_file, $source_file);
            if (!$data) {
                $data = $this->parse($source_file, $scene);

                // 回写入缓存
                $this->_cache->write("$this->_name.$scene.php", '<?php
return ' . var_export($data, true) . ';');
            }
        } else {
            $data = $this->parse($source_file, $scene);
        }

        return $data;
    }

    /**
     *
     * @param string $cache_file
     * @return ?array
     */
    protected function loadByCache(string $cache_file, string $source_file): ?array {
        $data = null;

        if (combi()->is_prod()) {
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
     * @param string $scene
     * @return ?array
     */
    protected function parse(string $source_file, string $scene): ?array {
        $raw = Neon::decode(file_get_contents($source_file));
        return $raw ? $this->makeupByScene($raw, $scene) : null;
    }

    /**
     *
     * @param array $raw
     * @param string $scene_selected
     * @return array
     */
    protected function makeupByScene(array $raw, string $scene_selected): array {
        $common = [];
        $scened = [];
        foreach ($raw as $section => $data) {
            if (strpos($section, ' < ')) {
                [$scene, $section] = explode(' < ', $section);
                if ($scene != $scene_selected) {
                    continue;
                }
                $scened[$section] = $data;
            } else {
                $common[$section] = $data;
            }
        }

        $ac = new ArrayCover($common);
        return $ac($scened);
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
