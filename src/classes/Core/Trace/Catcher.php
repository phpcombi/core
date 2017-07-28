<?php

namespace Combi\Core\Trace;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 * @todo 在全局违例捕获里暂不记日志，以防出现死循环。这里将设计一个额外方案，使用不同的通道记录违例
 */
class Catcher
{
    use core\Traits\Singleton;

    /**
     * @var array
     */
    protected $print_exc = false;

    public function __construct(bool $print_exc) {
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
        $this->print_exc = $print_exc;
    }

    /**
     *
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @param string $context
     * @throws ErrorException
     * @return void
     */
    public function errorHandler(int $severity, string $message,
        string $file, int $line, array $context): void {

        $exc = new ErrorException($message, 0, $severity, $file, $line);
        $exc->setContext($context);
        throw $exc;
    }

    /**
     *
     * @param \Error $throwable
     * @param bool $exit
     */
    public function exceptionHandler(\Throwable $throwable, $exit = true): void {

        // 处理方案
        // 记日志
        // TODO: 暂时记error，之后再改
        helper::error($throwable->getMessage(), ['exception' => $throwable]);

        // 是否输出
        // 如果是非生产环境条件允许打印输出
        if (!core::isProd() && $this->isPrintable($throwable)) {
            $this->printThrown($throwable);
        }

        if ($exit) {
            die(1);
        }
    }

    /**
     *
     * @param \Throwable $throwable
     * @todo 这里用 helper::du()不是最合适
     */
    protected function printThrown(\Throwable $throwable) {
        if ($throwable instanceof abort) {
            $context = $throwable->all();
            $throwable  = $throwable->getPrevious();
        } elseif ($throwable instanceof ErrorException) {
            $context = $throwable->getContext();
        } else {
            $context = [];
        }
        $sample     = new ExceptionSample('info', $throwable, $context);
        helper::du($sample->render(), "Ooooooooooooooooooops!!");
    }

    protected function isPrintable(\Throwable $throwable): bool {
        if ($this->print_exc) {
            return true;
        }
        return false;
    }

}
