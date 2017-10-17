<?php

namespace Combi\Core\Logger;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

use Combi\Core\Resource\Directory;

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

    public function __construct(string $baseDir, string $dateFormat = 'Y-m',
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

        $dir = new Directory($this->baseDir);
        $filename = $record['datetime']->format($this->dateFormat).
            DIRECTORY_SEPARATOR.$this->makeBasename($record['extra']['throwable']).'.txt';
        $dir->writeWhenNotExists($filename, function() use ($record): string {
            $throwable = $record['extra']['abort']
                ?? $record['extra']['throwable'];

            $sample = new Core\Trace\ThrowableSample($throwable, $record['context']);
            return $sample->render();
        });
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
