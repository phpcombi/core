<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

use Combi\Utils\ArrayCover;
use Nette\Neon\Neon;

/**
 * Description of Config
 *
 * @author andares
 */
class Config extends Meta\Collection
    implements \ArrayAccess
{
    use Meta\Extensions\Overloaded,
        Meta\Extensions\ArrayAccess;

    protected static $_methodSpaces = [
        'Combi\\Core\\Config\\Methods',
    ];

    protected static $_methodLoaded = [];

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

    public static function appendMethodSpaces(...$spaces): void {
        self::$_methodSpaces += $spaces;
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
        $sourceFile = $this->_source->select("$this->_name.neon");
        if (!file_exists($sourceFile)) {
            return [];
        }

        // 检查缓存
        if ($this->_cache) {
            // 缓存文件名
            $cacheFile = $this->_cache->select("$this->_name.$this->_scene.php");

            // 从缓存中读取
            $data = $this->loadByCache($cacheFile, $sourceFile);
            if (!$data) {
                // 回写入缓存
                $this->_cache->write("$this->_name.$this->_scene.php",
                    "<?php\nreturn ".var_export($this->parse($sourceFile), true).';');

                // 重新读出，为了实现method计算
                $data = $this->loadByCache($cacheFile, $sourceFile);
            }
        } else {
            $data = $this->parse($sourceFile);
        }

        return $data;
    }

    /**
     *
     * @param string $cacheFile
     * @return array
     */
    protected function loadByCache(string $cacheFile, string $sourceFile): array {
        $data = [];

        if (rt::isProd()) {
            // 生产环境直接载入
            $data = @include $cacheFile;
        } else {
            // 非生产环境检查更新情况
            if (file_exists($cacheFile)) {
                @clearstatcache(false, $sourceFile);
                @clearstatcache(false, $cacheFile);
                if (filemtime($sourceFile) <= filemtime($cacheFile)) {
                    $data = @include $cacheFile;
                }
            }
        }

        return $data;
    }

    /**
     *
     * @param string $sourceFile
     * @return array
     */
    protected function parse(string $sourceFile): array {
        $raw = Neon::decode(file_get_contents($sourceFile));
        $raw && $this->prune($raw);
        // TODO: 暂时不考虑遍历算法与修剪合并
        $raw && $this->traverse($raw);
        return $raw;
    }

    protected function traverse(array &$arr,
        $parentKey = null, array &$parentArr = null)
    {
        foreach (new \RecursiveArrayIterator($arr) as $key => $value) {
            if (is_array($value)) { // 进入下一级
                $this->traverse($arr[$key], $key, $arr);
            }
            // 扩展方法
            if ($key[0] == '$') {
                $method = $this->getMethod(substr($key, 1), [$arr[$key]]);
                $parentArr[$parentKey] = $this->_cache ? $method : $method();
                break; // 目前一个key下只允许一个配置方法
            }

        }
    }

    protected function getMethod(string $name, $params) {
        if (!array_key_exists($name, self::$_methodLoaded)) {
            foreach (self::$_methodSpaces as $space) {
                $class = "$space\\".ucfirst($name);
                if (class_exists($class)) {
                    self::$_methodLoaded[$name] = $class;
                    break;
                }
            }
            !isset(self::$_methodLoaded[$name]) && self::$_methodLoaded[$name] = null;
        }
        if (self::$_methodLoaded[$name]) {
            return helper::make(self::$_methodLoaded[$name], $params);
        }
        return null;
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
