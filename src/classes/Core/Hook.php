<?php

namespace Combi\Core;

use Combi\Traits;

class Hook
{
    use Traits\Instancable;

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
        if (!isset($this->takers[$name]) || !isset($this->handlers[$name])) {
            return;
        }

        $taker = $this->takers[$name];
        $taker($this->handlers[$name], ...$args);
    }

}
