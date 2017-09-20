<?php

namespace Combi\Core\Config\Methods;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class Path extends Core\Config\Method
{
    protected $path;
    protected $category     = '';
    protected $pid          = null;
    protected $autoCreate   = false;

    public function __invoke(): string {
        $pid    = $this->pid ?: 'main';
        $path   = rt::$pid()->path($this->category, $this->path);

        $this->autoCreate && !\file_exists($path) && mkdir($path, 0777, true);
        return $path;
    }
}