<?php

namespace Combi\Core\Logger;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

use Monolog\Logger as MonoLogger;
use Monolog\Handler\AbstractHandler;

/**
 */
class SampleSnapHandler extends AbstractHandler
{
    const EXISTS_CACHE_LIMIT = 200;

    private static $dir_created = [];
    private static $exist_cache = [];
    private static $cache_id    = 0;

    private $base_dir;
    private $date_format;

    public function __construct(string $base_dir, string $date_format = 'Y-m',
        int $level = MonoLogger::INFO, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->base_dir     = $base_dir;
        $this->date_format  = $date_format;
    }

    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $this->write($record);

        return false === $this->bubble;
    }

    public function write(array $record): void {
        if (!($record['extra']['throwable'] ?? null)) {
            return;
        }

        $filename = $this->getFilename($record['extra'], $record['datetime']);
        if ($this->checkFileExists($filename)) {
            return;
        }

        $throwable = $record['extra']['abort']
            ?? $record['extra']['throwable'];

        $sample = new Core\Trace\ThrowableSample($throwable, $record['context']);
        @\file_put_contents($filename, $sample->render(), \LOCK_EX);
    }

    private function getFilename(array $extra,
        \DateTimeInterface $datetime): string
    {
        $dir = rt::core()->path('logs', $this->base_dir).DIRECTORY_SEPARATOR.
            $datetime->format($this->date_format);
        $this->createDir($dir);

        return $dir.DIRECTORY_SEPARATOR.
            $this->makeBasename($extra['throwable']).'.txt';
    }

    private function createDir(string $dir): void {
        if (isset(self::$dir_created[$dir])) {
            return;
        }

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \UnexpectedValueException(
                    "There is no existing directory at $dir");
            }
        }

        self::$dir_created[$dir] = true;
    }

    private function checkFileExists(string $filename): bool {
        if ((self::$exist_cache[self::$cache_id] ?? null) == $filename) {
            return true;
        }

        self::$exist_cache[self::$cache_id] = $filename;
        (++self::$cache_id) && (self::$cache_id > self::EXISTS_CACHE_LIMIT)
            && self::$cache_id = 0;

        return file_exists($filename);
    }

    private function makeBasename(\Throwable $throwable): string {
        // 文件名结构
        $name = $throwable->getCode().'-'.
            strtr(get_class($throwable), '\\', '_').'-'.
            basename($throwable->getFile()).'-'.
            $throwable->getLine();

        return $name;
    }

}
