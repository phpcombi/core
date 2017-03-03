<?php

namespace Combi\Utils;

use Combi\Core\Config;

class Dictionary extends Config
{
    public function __invoke($key, ...$values): string {
        return $this->translate($key, ...$values);
    }

    public function translate($key, ...$values): string {
        return $values ? $this[$key] : sprintf($this[$key], ...$values);
    }
}
