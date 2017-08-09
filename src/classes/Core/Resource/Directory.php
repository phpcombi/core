<?php

namespace Combi\Core\Resource;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 * @todo 缓存问题之后考虑
 */
class Directory implements core\Interfaces\Resource, \IteratorAggregate
{
    use core\Traits\Instancable;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var array
     */
    private $replaces = [];

    public function __construct(string $dir) {
        $this->dir      = $dir;
    }

    public function read($path) {
        $file = $this->select($path);
        return $file ? file_get_contents($file) : '';
    }

    public function writeWhenNotExists($path, $data) {
        $file   = $this->select($path);
        if ($file && file_exists($file)) {
            return $file;
        }

        // 生成数据
        is_callable($data) && $data = $data();
        if (is_bool($data)) {
            return $data;
        }

        $dir    = dirname($file);
        !file_exists($dir) && mkdir($dir, 0755, true);
        if (file_put_contents($file, $data, \LOCK_EX)) {
            return $file;
        }
        return null;
    }

    public function write($path, $data) {
        $file   = $this->select($path);

        $dir    = dirname($file);
        !file_exists($dir) && mkdir($dir, 0755, true);
        if (file_put_contents($file, $data, \LOCK_EX)) {
            return $file;
        }
        return null;
    }

    public function exists($path): bool {
        $file = $this->select($path);
        return $file && file_exists($file);
    }

    /**
     * @param string $path
     * @return string
     */
    public function select($path): string {
        if (isset($this->replaces[$path])) {
            return $this->replaces[$path];
        }

        $file = $this->dir.DIRECTORY_SEPARATOR.$path;
        return $file;
    }

    public function traversal(): iterable {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir));
        foreach ($it as $fileinfo) {
            /* @var $fileinfo \SplFileInfo */
            if ($fileinfo->isDir()) {
                continue;
            }
            yield $fileinfo;
        }
    }

    public function replace($path, $data): void {
        $this->replaces[$path] = $data;
    }

    /**
     *
     * @return iterable
     */
    public function getIterator() {
        return $this->traversal();
    }
}
