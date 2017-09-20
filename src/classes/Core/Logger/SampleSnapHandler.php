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

    private static $dirCreated  = [];
    private static $existCache  = [];
    private static $cacheId     = 0;

    private $baseDir;
    private $dateFormat;

    public function __construct(string $baseDir, string $dateFormat = 'c',
        int $level = MonoLogger::INFO, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->baseDir     = $baseDir;
        $this->dateFormat  = $dateFormat;
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
        $dir = $this->baseDir.DIRECTORY_SEPARATOR.
            $datetime->format($this->dateFormat);
        $this->createDir($dir);

        return $dir.DIRECTORY_SEPARATOR.
            $this->makeBasename($extra['throwable']).'.txt';
    }

    private function createDir(string $dir): void {
        if (isset(self::$dirCreated[$dir])) {
            return;
        }

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \UnexpectedValueException(
                    "There is no existing directory at $dir");
            }
        }

        self::$dirCreated[$dir] = true;
    }

    private function checkFileExists(string $filename): bool {
        if ((self::$existCache[self::$cacheId] ?? null) == $filename) {
            return true;
        }

        self::$existCache[self::$cacheId] = $filename;
        (++self::$cacheId) && (self::$cacheId > self::EXISTS_CACHE_LIMIT)
            && self::$cacheId = 0;

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
