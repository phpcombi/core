<?php

namespace Combi\Core\Interfaces;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

interface Resource
{
    public function read($path);
    public function write($path, $data);
    public function exists($path): bool;
    public function traversal(): iterable;

    /**
     * replace()只需要对 read() 与 write() 有效即可
     *
     * @param mixed $path
     * @param mixed $data
     */
    public function replace($path, $data);
}
