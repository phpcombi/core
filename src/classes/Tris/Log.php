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
        if (is_object($this->message)) {
            if ($this->message instanceof abort) {
                // 这里取出的 $exc 下面会用到
                $exc = $this->message->getPrevious();

                $message = $exc->getMessage();
                $context = $this->context
                    ? array_merge($this->context, $this->message->all())
                    : $this->message->all();

            } elseif ($this->message instanceof \Throwable) {
                $exc = $this->message;

                $message = $exc->getMessage();
                $context = $this->context;
            } else {
                throw abort::invalidAgrument("log message must implements \Throwable when it was object")
                    ->set('class', get_class($this->message));
            }
        } else {
            $exc = null;
            $message = $this->message;
            $context = $this->context;
        }

        if (!defined("Psr\Log\LogLevel::" . strtoupper($level))) {
            $level = $exc ? $this->gainLevelByException($exc) : Level::DEBUG;
        }

        $exc && !isset($context['exception']) && $context['exception'] = $exc;

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
