<?php

namespace Combi\Tris;

class ErrorException extends \ErrorException
{
    /**
     * @var array
     */
    protected $context = [];

    public function setContext(array $context): void {
        $this->context = $context;
    }

    public function getContext(): array {
        return $this->context;
    }

}
