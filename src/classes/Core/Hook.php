<?php

namespace Combi\Core;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Traits;

class Hook
{
    use Traits\Singleton;

    /**
     *
     * @var callable[]
     */
    private $takers = [];

    /**
     *
     * @var callable[]
     */
    private $handlers = [];

    public function add(string $name, callable $taker = null): self {
        if (!$taker) {
            $taker = function(array $handlers, ...$args) {
                foreach ($handlers as $handler) {
                    $handler(...$args);
                }
                return null;
            };
        }
        $this->takers[$name] = $taker;

        return $this;
    }

    public function attach(string $name, callable $handler): self {
        $this->handlers[$name][] = $handler;

        return $this;
    }

    public function take(string $name, ...$args) {
        if (!isset($this->takers[$name])) {
            throw abort::unexpectedValue("hook {name} is undefined")
                ->set('name', $name);
        }

        $taker = $this->takers[$name];
        return $taker($this->handlers[$name] ?? [], ...$args);
    }

}
