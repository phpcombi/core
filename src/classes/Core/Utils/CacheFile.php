<?php

namespace Combi\Core\Utils;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Combi\Core\Resource;

/**
 *
 * @author andares
 */
class CacheFile
{
    /**
     * @var Resource\Directory
     */
    public $dir;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var callable
     */
    public $maker;

    public function __construct(callable $dir, callable $filename,
        callable $maker)
    {
        $this->dir      = $dir();
        $this->filename = $filename();

        $this->maker    = $maker;
    }

    public function load(callable $rebuild_check = null) {
        $maker = $this->maker;

        if ($rebuild_check) {
            if ($rebuild_check($this)) {
                if (!($file = $this->dir->write($this->filename, $maker()))) {
                    throw new \RuntimeException("Cache file can not create");
                }
            } else {
                $file = $this->dir->select($this->filename);
            }
        } else {
            if (!($file = $this->dir->writeWhenNotExists($this->filename, $maker))) {
                throw new \RuntimeException("Cache file can not create");
            }
        }
        return include $file;
    }
}
