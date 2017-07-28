<?php

namespace Combi\Core\Trace;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Psr\Log\LogLevel as Level;

class Log extends core\Meta\Collection
{
    /**
     * @param string $level
     * @return void
     */
    protected function apply(string $level): void {
        if ($this->_message instanceof abort) {
            $context = array_merge($this->all(), $this->_message->all());
            $message = $this->_message->getPrevious();
        } else {
            $context = $this->all();
            $message = $this->_message;
        }

        if (!defined("Psr\Log\LogLevel::" . strtoupper($level))) {
            $level = $message instanceof \Throwable
                ? $this->gainLevelByException($message) : Level::DEBUG;
        }

        helper::log($message, $context, $level, $this->_channel);
    }

    protected function gainLevelByException(\Throwable $exc): string {
        if ($exc instanceof \LogicException) {
            $level = Level::NOTICE;
        } elseif ($exc instanceof \RuntimeException) {
            $level = Level::CRITICAL;
        } elseif ($exc instanceof \ErrorException
            || $exc instanceof \Error) {

            $level = Level::ERROR;
        } else {
            $level = Level::WARNING;
        }
        return $level;
    }
}
