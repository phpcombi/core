<?php

namespace Combi\Tris;

use Psr;
use ErrorException;
use Combi\Traits;
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
     * @var \DateTimeZone
     */
    protected $timezone = null;

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
     * @param \DateTimeZone $timezone
     * @return void
     */
    public function setTimeZone(\DateTimeZone $timezone): void {
        $this->timezone = $timezone;
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimeZone(): \DateTimeZone {
        !$this->timezone
            && $this->timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        return $this->timezone;
    }

    /**
     * 如果message是一个对象，那么其必须有```__toString()```方法。
     *
     * 支持```{key}```和```:key```两种风格的替换符，可以传```$context```参数作为一个数组，
     * message中的key将会使用数组中对应键值的单元替换掉。
     *
     * 替换符只支持```[A-Za-z0-9_\.]```这些字符。
     *
     * 如果需要通过context传违例，推荐放在名为```exception```的key下。
     * 如果是Error对象，可以放在```error```或```exception```的key下。
     *
     * 如果自定义不要记录样本，可以context里传sampless=true
     *
     * ```level```值推荐使用```Psr\Log\LogLevel```的常量
     *
     * 如果是\ErrorException，会尝试记录错误号到context.serverity
     * 如果是ErrorException，会尝试记录错误context到context.debug_vars
     *
     * @param string $level
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        // 参数初始化
        $datetime   = new \DateTimeImmutable('now', $this->getTimeZone());

        $exception  = $context['exception'] ?? $context['error'] ?? null;
        if ($exception instanceof \Throwable) {
            $exception instanceof \ErrorException
                && !isset($context['severity'])
                    && $context['severity'] = $exception->getSeverity();

            $exception instanceof ErrorException
                && !isset($context['debug_vars'])
                    && $context['debug_vars'] = $exception->getContext();
        } else {
            $exception = null;
        }
        unset($context['exception']);
        unset($context['error']);

        // 触发勾子处理context
        $context = combi()->core->hook->take(\Combi\HOOK_LOG_CONTEXT, $context);

        // 构造数据
        $record = [
            'datetime'  => $datetime,
            'channel'   => $this->name,
            'level'     => $level,
            'message'   => $message,
            'context'   => $context,
            'exception' => $exception,
        ];

        $this->to_file && $this->toFile($record);

        // 触发勾子扩展
        combi()->core->hook->take(\Combi\HOOK_LOG, $record);
    }

    /**
     * @param array $record
     * @return void
     */
    protected function toFile(array $record): void {
        // 获取路径
        $path = $this->getLogPath($record['datetime']);

        // 样本记录
        if ($record['exception']
            && (!isset($record['context']['sampless'])
                || !$record['context']['sampless'])) {

            $sample = new ExceptionSample("exc", $record['exception'], $record['context']);
            $sample->setLevel($record['level']);
            $sample_file = $sample->save($path);
        } else {
            $sample_file = null;
        }

        // 日志内容处理
        unset($record['exception']);
        $record['datetime'] = $record['datetime']->format($this->datetime_format);

        (is_string($record['message']) && $record['context'])
            && $record['message'] = \combi\padding($record['message'], $record['context']);

        $sample_file && $record['sample'] = $sample_file;

        // 记日志
        $line = json_encode($record);
        $file = $path . \combi\padding($this->file_suffix, $record);
        if (!@file_put_contents($file,
            $line . \PHP_EOL, FILE_APPEND | LOCK_EX)) {

            throw new \RuntimeException("unable write to log file [$file]");
        }
    }

    /**
     * @param \DateTimeImmutable $datetime
     * @return string
     */
    protected function getLogPath(\DateTimeImmutable $datetime): string {
        return $this->getBaseDir() . DIRECTORY_SEPARATOR . $datetime->format($this->slice_format);
    }

    /**
     * @return string
     */
    protected function getBaseDir(): string {
        return combi()->core->path('logs', $this->name);
    }
}
