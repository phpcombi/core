<?php
namespace Combi;

use Psr\Log\LogLevel as Level;

class Log
{
    /**
     * @var string
     */
    protected $level;

    /**
     * @var string|int|Abort|\Throwable
     */
    protected $message;

    /**
     * @var array
     */
    protected $context;

    /**
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public static function __callStatic(string $name, array $arguments = []) {
        $log = new static($name, ...$arguments);
        $log->apply();
        return $log;
    }

    /**
     * @param string $level
     * @param string|int|Abort|\Throwable $message
     * @param array $context
     */
    public function __construct(string $level, $message, array $context = []) {
        $this->level    = $level;
        $this->message  = $message;
        $this->context  = $context;
    }

    /**
     * @return void
     */
    public function apply(): void {
        if (is_object($this->message)) {
            if ($this->message instanceof Abort) {
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
                throw abort(new \InvalidArgumentException(
                    "log message must implements \Throwable when it was object"))
                    ->set('class', get_class($this->message));
            }
        } else {
            $exc = null;
            $message = $this->message;
            $context = $this->context;
        }

        if (defined("Psr\Log\LogLevel" . strtoupper($this->level))) {
            $level = $this->level;
        } else {
            $level = $exc ? $this->gainLevelByException($exc) : Level::DEBUG;
        }

        $exc && !isset($context['exception']) && $context['exception'] = $exc;

        Tris::log($level, $message, $context);
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
