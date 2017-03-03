<?php

namespace Combi\Tris;

class ExceptionSample
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var \Throwable
     */
    private $thrown;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var !string
     */
    private $level = null;

    /**
     * @var string
     */
    private $template = "
- message: {0}
- code: {1}
- file: {2}
- line: {3}
- info:
{4}
<<<<<<<<

{5}
";

    /**
     * @var string
     */
    private $joiner = "\n++++++++++++++++\n";

    public function __construct(string $type, \Throwable $thrown, array $context = []) {
        $this->type     = $type;
        $this->thrown   = $thrown;
        $this->context  = $context;
    }

    public function setLevel($level): void {
        $this->level = $level;
    }

    public function setTemplate(string $template, ?string $joiner = null): self {
        $this->template = $template;
        $joiner && $this->joiner = $joiner;
        return $this;
    }

    public function save(string $dir, bool $force = false): ?string {
        if (!$force && !$this->isAllowSave()) {
            return null;
        }

        !file_exists($dir) && @mkdir($dir, 0755, true);

        $file = $dir . DIRECTORY_SEPARATOR . $this->makeFileName() . ".smp";
        !file_exists($file)
            && @file_put_contents($file, $this->render(), LOCK_EX);

        return $file;
    }

    public function render(): string {
        $current = \combi\padding($this->template, $this->makeVars($this->thrown, $this->context));
        $exc = $this->thrown->getPrevious();
        if ($exc) {
            $previous = \combi\padding($this->template, $this->makeVars($exc));
        } else {
            $previous = '';
        }

        return "$current$this->joiner$previous";
    }

    private function isAllowSave(): bool {
        $config = combi()->core->config('tris')['sample'];
        if ($this->level && !isset($config['levels'][$this->level])) {
            return false;
        }

        if (isset($config['whitelist'])
            && $config['whitelist']) { // 进入白名单规则

            if (isset($config['whitelist'][$this->thrown->getCode()])) {
                return true;
            }
            return false;
        }

        if (isset($config['blacklist'])
            && $config['blacklist']) { // 进入黑名单规则

            if (isset($config['blacklist'][$this->thrown->getCode()])) {
                return false;
            }
            return true;
        }

        return true;
    }

    private function makeVars(\Throwable $exc, ?array $context = null): array {
        return [
            $exc->getMessage(),
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
            $this->type,
            $this->thrown->getCode(),
            get_class($this->thrown),
            basename($this->thrown->getFile()) . ":" . $this->thrown->getLine(),
            $this->context['primary'] ?? '',
            substr(md5(preg_replace('~(Resource id #)\d+~', '$1', $this->thrown)), 0, 5),
        ];

        // primary限长
        strlen($name[4]) > 10 && $name[4] = substr($name[4], 0, 10);
        return implode('-', $name);
    }
}
