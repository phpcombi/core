<?php

namespace Combi\Core\Logger;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 */
class RichMessageProcessor
{
    use core\Traits\Singleton;

    private $autolevel;

    public function __construct(bool $autolevel) {
        $this->autolevel = $autolevel;
    }

    /**
     * 日志消息富类型处理。
     *
     * 对message为对象或是数组进行判定，并将其转化为字串。
     * message padding支持。
     * 根据message的类型和内容，为 context 添加 debugvars, throwable 和 raw 三个字段。
     *
     * @param mixed $message
     * @param array $context
     * @return array
     */
    public function __invoke(array $record): array {
        if (!isset($record['context']['__richmsg'])) {
            return $record;
        }

        $message = $record['context']['__richmsg'];
        unset($record['context']['__richmsg']);
        $context = $record['context'];

        // 处理message
        $abortinfo = null;
        if (is_object($message)) {
            if ($message instanceof \Throwable) {
                $raw = null;
                $throwable = $message;
                if ($throwable instanceof abort) {
                    $message = $throwable->message();

                    $abortinfo = $throwable->all();
                } else {
                    $message = $throwable->getMessage();

                    $context && $message = helper::padding($message, $context);
                }
            } elseif (method_exists($message, 'toArray')) {
                $raw = $message;
                $throwable = null;
                $message   = $raw->toArray();
            } elseif (method_exists($message, '__toString')) {
                $raw = $message;
                $throwable = null;
                $message   = (string)$raw;
            } else {
                $raw = $message;
                $throwable = null;
                $message   = helper::stringify($message);
            }
        } elseif (is_array($message)) {
            $raw = $message;
            $throwable = null;
            $message = helper::stringify($message);
        } else {
            $raw = null;
            $throwable = null;

            $context && $message = helper::padding((string)$message, $context);
        }

        // 处理debugvars
        $debugvars  = ($throwable instanceof ErrorException)
            ? $throwable->getContext() : [];
        // 原生 ErrorException 取 Severity 值
        ($throwable instanceof \ErrorException)
            && $debugvars['__severity'] = $throwable->getSeverity();

        $raw        && $record['extra']['raw']       = $raw;
        $throwable  && $record['extra']['throwable'] = $throwable;
        $debugvars  && $record['extra']['debugvars'] = $debugvars;
        $abortinfo  && $record['extra']['abortinfo'] = $abortinfo;

        $record['message'] = $message;
        return $record;
    }

}
