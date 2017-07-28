<?php

namespace Combi\Core\Trace;

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
