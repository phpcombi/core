<?php

namespace Combi\Tris;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

class ExceptionSample
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var \Throwable
     */
    private $thrown;

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
    private $template = "\n- message: {0}\n- code: {1}\n- file: {2}\n- line: {3}\n- info:\n{4}\n<<<<<<<<\n\n{5}\n";

    /**
     * @var string
     */
    private $joiner = "\n^--------------^\n";

    public function __construct(string $level, \Throwable $thrown, array $context = []) {
        $this->level    = $level;
        $this->thrown   = $thrown;
        $this->context  = $context;
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
            $this->makeVars($this->thrown, $this->context));
        $exc = $this->thrown->getPrevious();
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
            $context = helper::object2array($context);
            unset($context['GLOBALS']);
        }
        return [
            $context
                ? helper::padding($exc->getMessage(), $context)
                : $exc->getMessage(),
            $exc->getCode(),
            $exc->getFile(),
            $exc->getLine(),
            $context ? json_encode($context) : '{}',
            $exc->getTraceAsString(),
        ];
    }

    private function makeFileName(): string {
        // 文件名结构
        $name = [
            $this->thrown->getCode(),
            get_class($this->thrown),
            basename($this->thrown->getFile()),
            $this->thrown->getLine(),
        ];
        isset($this->context['primary']) && $name[] = $this->context['primary'];

        // primary限长
        isset($name[4]) && strlen($name[4]) > 10 && $name[4] = substr($name[4], 0, 10);
        return $this->prefix.implode('-', $name);
    }
}
