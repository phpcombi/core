<?php

namespace Combi\Core\Trace;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

class ThrowableSample
{
    /**
     * @var \Throwable
     */
    private $throwable;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $context;

    /**
     * @var array|null
     */
    private $more;

    /**
     * @var string
     */
    private $template = "\n- message: {{0}}\n- code: {{1}}\n- file: {{2}}\n- line: {{3}}\n- info:\n{{4}}\n\n- more:\n{{5}}\n<<<<<<<<\n\n{{6}}\n";

    /**
     * @var string
     */
    private $joiner = "\n^--------------^\n";

    public function __construct(\Throwable $throwable, array $more = null) {
        if ($throwable instanceof Core\Abort) {
            $this->message = $throwable->message();

            $context    = $throwable->all();
            $throwable  = $throwable->getPrevious();
        } elseif ($throwable instanceof ErrorException) {
            $this->message = $throwable->getMessage();

            $context = $throwable->getContext();
        } else {
            $this->message = $throwable->getMessage();

            $context = [];
        }

        $this->throwable = $throwable;
        $this->context   = $context;
        $this->more      = $more;
    }

    public function setTemplate(string $template, ?string $joiner = null): self {
        $this->template = $template;
        $joiner && $this->joiner = $joiner;
        return $this;
    }

    public function save(string $dir): string {
        !file_exists($dir) && @mkdir($dir, 0755, true);

        $file = $dir.DIRECTORY_SEPARATOR.$this->makeFileName().".smp";
        !file_exists($file)
            && @file_put_contents($file, $this->render(), LOCK_EX);

        return $file;
    }

    public function render(): string {
        $current = helper::padding($this->template,
            $this->makeVars($this->throwable, $this->context, $this->more));
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
    private function makeVars(\Throwable $exc,
        ?array $context = null, ?array $more = null): array
    {
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
            $more   ? helper::stringify($more) : '{}',
            $exc->getTraceAsString(),
        ];
    }
}
