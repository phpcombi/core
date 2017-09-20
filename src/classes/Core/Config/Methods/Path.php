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
    protected $category = '';
    protected $pid      = null;

    public function __invoke(): string {
        $pid = $this->pid ?: 'main';
        return rt::$pid()->path($this->category, $this->path);
    }
}