<?php

namespace Combi\Tris;

use Psr;
use Combi\Traits;
use Combi\Utils\Pack;

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
    protected $format = 'c';

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct(string $name, array $config = [])
    {
        $this->name = $name;
        ['to_file' => $this->to_file, 'format' => $this->format] = $config;
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
     *
     * ```level```值推荐使用```Psr\Log\LogLevel```的常量
     */
    public function log(string $level, $message, array $context = []): void
    {
        $datetime = new \DateTimeImmutable('now', $this->getTimeZone());

        $record = [
            'datetime'  => $datetime,
            'channel'   => $this->name,
            'level'     => $level,
            'message'   => $message,
            'context'   => $context,
        ];

        $this->to_file && $this->toFile($record);

        // 触发勾子扩展
        combi()->core->hook->take(\Combi\HOOK_LOG, $record);
    }

    protected function toFile(array $record): void {
        // 日志内容处理
        $record['datetime'] = $record['datetime']->format($this->format);
        (is_string($record['message']) && $record['context'])
            && $record['message'] = \combi\padding($record['message'], $record['context']);

        // 获取路径
        $path = $this->getLogPath($record['datetime']);

        // 记日志
        $line = Pack::encode('json', $record);
        if (!@file_put_contents("$path.log", $line . \PHP_EOL, FILE_APPEND | LOCK_EX)) {
            throw new \RuntimeException("unable write to log file [$path.log]");
        }

        // 样本
    }

    protected function getLogPath(\DateTimeImmutable $datetime): string {
        return combi()
            ->core
            ->path('logs',
                "tris" . DIRECTORY_SEPARATOR . $datetime->format('Y.W'));
    }
}
