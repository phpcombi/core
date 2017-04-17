<?php

namespace Combi\Tris;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Psr;
use ErrorException;
use Combi\Common\Traits;
use Combi\Common\Interfaces;
use Combi\Core\Resource;

/**
 * log系统偏向底层，也不需要替换资源，因此目前没有使用 Resource\Directory 类对文件进行操作。
 */
class Logger extends \Psr\Log\AbstractLogger
{
    use Traits\Instancable;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $to_file = true;

    /**
     * @var string
     */
    protected $file_suffix = '.log';

    /**
     * @var string
     */
    protected $datetime_format = 'c';

    /**
     * @var string
     */
    protected $slice_format = 'Y.W';

    /**
     * @var array
     */
    protected $sample_conf  = [];

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct(string $name, array $config = [])
    {
        $this->name = $name;
        [
            'to_file'           => $this->to_file,
            'file_suffix'       => $this->file_suffix,
            'datetime_format'   => $this->datetime_format,
            'slice_format'      => $this->slice_format,
        ] = $config;

        // 初始化目录
        $base_dir = $this->getBaseDir();
        !file_exists($base_dir) && @mkdir($base_dir, 0755, true);
    }

    /**
     *
     * -   message允许接收对象、数组或简单类型
     *     -   是对象，有```__toString()```方法直接转换，没有通过```helper::object2array()```转为数组
     *     -   message非对象，则(string)转为字串
     * -   message为\Throwable时，会获取其message作为消息主体
     * -   message为字符串时，会根据context进行```helper::padding()```处理
     *     -   支持```{name}```和``` :name ```两种占位符
     *     -   占位符名称规则为```[A-Za-z0-9_\.]```
     * -   context中的特定键名包括了:
     *     -   exception: 违例或Error对象，该键会在捕获后被移除。
     *     -   error: Error对象，该键会在捕获后被移除。
     *     -   datetime: 自定义的\DateTimeInterface对象。
     *     -   primary: primary作为关键id，目前用于记录违例样本的文件名命名中
     *     -   sampless: 设置true将不记录违例样本
     *
     * @param string $level
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        // 构造数据
        $record = $this->prepare($message, $context);
        $record['level'] = $level;

        // 触发预处理勾子
        $to_file_path = $this->getLogPath($record['datetime']);
        $record = core::hook()->take(\Combi\HOOK_LOG_PREPARE,
            $record, $to_file_path);

        // 自带写文件
        $this->to_file && $this->toFile($to_file_path, $record);

        // 触发勾子扩展
        core::hook()->take(\Combi\HOOK_LOG, $record);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return array
     */
    protected function prepare($message, array $context): array {
        // 处理message
        if (is_object($message)) {
            if ($message instanceof \Throwable) {
                $raw = null;
                $exception = $message;
                $message   = $exception->getMessage();

                $context && $message = helper::padding($message, $context);
            } elseif ($message instanceof Interfaces\Arrayable) {
                $raw = $message;
                $exception = null;
                $message   = $raw->toArray();
            } elseif (method_exists($message, '__toString')) {
                $raw = $message;
                $exception = null;
                $message   = (string)$raw;
            } else {
                $raw = $message;
                $exception = null;
                $message   = helper::object2array($message);
            }
        } else {
            $raw = null;
            $exception = null;

            $context && $message = helper::padding((string)$message, $context);
        }

        // 处理exception
        if (!$exception) {
            if (isset($context['exception'])) {
                $exception = $context['exception'];
            } elseif (isset($context['error'])) {
                $exception = $context['error'];
            }
        }

        // 处理debugvars
        if ($exception instanceof ErrorException) {
            $debugvars  = $exception->getContext();
        } else {
            $debugvars  = [];
        }
        $exception instanceof \ErrorException
            && $debugvars['__severity'] = $exception->getSeverity();

        $record = [
            'datetime'  => $context['datetime'] ?? $this->getDateTime(),
            'channel'   => $this->name,
            'message'   => $message,
            'context'   => $context,
            'primary'   => $context['primary'] ?? 0,
            'debugvars' => $debugvars,
        ];
        $exception && $record['exception'] = $exception;
        $raw && $record['raw'] = $raw;
        return $record;
    }

    /**
     * @return \DateTimeInterface
     */
    protected function getDateTime(): \DateTimeInterface {
        return core::now() ?: new \DateTimeImmutable('now');
    }

    /**
     * @param string $path
     * @param array $record
     * @return string
     */
    protected function toFile(string $path, array $record): string {
        // 日期时间处理
        $record['datetime'] = $record['datetime']->format($this->datetime_format);

        // 记日志
        $line = json_encode($record);
        $file = $path.helper::padding($this->file_suffix, $record);
        if (!@file_put_contents($file,
            $line . \PHP_EOL, FILE_APPEND | LOCK_EX)) {

            throw new \RuntimeException("unable write to log file [$file]");
        }

        return $file;
    }

    /**
     * @param \DateTimeInterface $datetime
     * @return string
     */
    protected function getLogPath(\DateTimeInterface $datetime): string {
        return $this->getBaseDir().DIRECTORY_SEPARATOR.
            $datetime->format($this->slice_format);
    }

    /**
     * @return string
     */
    protected function getBaseDir(): string {
        return core::path('logs', $this->name);
    }
}
