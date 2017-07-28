<?php

namespace Combi\Core\Trace;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

class ExceptionSample
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var \Throwable
     */
    private $throwable;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var string
     */
    private $level = '';

    /**
     * @var string|int
     */
    private $primary = '';

    /**
     * @var string
     */
    private $template = "\n- message: %0%\n- code: %1%\n- file: %2%\n- line: %3%\n- info:\n%4%\n<<<<<<<<\n\n%5%\n";

    /**
     * @var string
     */
    private $joiner = "\n^--------------^\n";

    public function __construct(string $level, \Throwable $throwable, array $context = []) {
        $this->level     = $level;
        $this->throwable = $throwable;
        $this->context   = $context;
    }

    public static function createByRecord(array $record): self {
        return (new self($record['level'], $record['exception'], $record['context']))
            ->setPrimary($record['primary']);
    }

    public function setPrimary($primary): self {
        $this->primary = $primary;
        return $this;
    }

    public function setPrefix($prefix): self {
        $this->prefix = $prefix;
        return $this;
    }

    public function setTemplate(string $template, ?string $joiner = null): self {
        $this->template = $template;
        $joiner && $this->joiner = $joiner;
        return $this;
    }

    public function save(string $dir): string {
        !file_exists($dir) && @mkdir($dir, 0755, true);

        $file = $dir . DIRECTORY_SEPARATOR . $this->makeFileName() . ".smp";
        !file_exists($file)
            && @file_put_contents($file, $this->render(), LOCK_EX);

        return $file;
    }

    public function render(): string {
        $current = helper::padding($this->template,
            $this->makeVars($this->throwable, $this->context));
        $exc = $this->throwable->getPrevious();
        if ($exc) {
            $previous = helper::padding($this->template, $this->makeVars($exc));
        } else {
            $previous = '';
        }

        return "$current$this->joiner$previous";
    }

    /**
     *
     * @param \Throwable $exc
     * @param array|null $context
     * @return array
     */
    private function makeVars(\Throwable $exc, ?array $context = null): array {
        if ($context) {
            unset($context['GLOBALS']);
        }
        return [
            $context
                ? helper::padding($exc->getMessage(), $context)
                : $exc->getMessage(),
            $exc->getCode(),
            $exc->getFile(),
            $exc->getLine(),
            $context ? helper::stringify($context) : '{}',
            $exc->getTraceAsString(),
        ];
    }

    private function makeFileName(): string {
        // 文件名结构
        $name = [
            $this->throwable->getCode(),
            get_class($this->throwable),
            basename($this->throwable->getFile()),
            $this->throwable->getLine(),
        ];
        isset($this->context['primary']) && $name[] = $this->context['primary'];

        // primary限长
        isset($name[4]) && strlen($name[4]) > 10 && $name[4] = substr($name[4], 0, 10);
        return $this->prefix.implode('-', $name);
    }
}
