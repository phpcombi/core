<?php

namespace Combi\Tris;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Psr\Log\LogLevel as Level;

class Log
{
    /**
     * @var string|int|abort|\Throwable
     */
    protected $message;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var string
     */
    protected $channel = 'default';

    /**
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public static function __callStatic(string $name, array $arguments) {
        $log = new static($name, ...$arguments);
        $log->$name();
        return $log;
    }

    /**
     * @param string|int|abort|\Throwable $message
     * @param array $context
     */
    public function __construct($message, array $context = []) {
        $this->message  = $message;
        $this->context  = $context;
    }

    /**
     * @param string $channel
     * @return self
     */
    public function setChannel(string $channel): self {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call(string $name, array $arguments) {
        return $this->apply($name);
    }

    /**
     * @param string $level
     * @return void
     */
    protected function apply(string $level): void {
        if ($this->message instanceof abort) {
            $context = array_merge($this->message->all());
            $message = $this->message->getPrevious();
        } else {
            $context = $this->context;
            $message = $this->message;
        }

        if (!defined("Psr\Log\LogLevel::" . strtoupper($level))) {
            $level = $message instanceof \Throwable
                ? $this->gainLevelByException($message) : Level::DEBUG;
        }

        tris::log($message, $context, $level, $this->channel);
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
