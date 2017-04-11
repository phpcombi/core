<?php

namespace Combi\Tris;

use Combi\Facades\Runtime as rt;
use Combi\Facades\Tris as tris;
use Combi\Facades\Helper as helper;
use Combi\Package as core;
use Combi\Package as inner;
use Combi\Core\Abort as abort;

use Combi\Traits;

/**
 * @todo 在全局违例捕获里暂不记日志，以防出现死循环。这里将设计一个额外方案，使用不同的通道记录违例
 */
class Catcher
{
    use Traits\Singleton;

    /**
     * @var array
     */
    protected $config = [];

    public function __construct(array $config) {
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);

        if ($config['shutdown_hook']) {
            register_shutdown_function([$this, 'shutdownHandler']);
        }

        $this->config = $config;
    }

    public function shutdownHandler() {
        core::hook()->take(\Combi\HOOK_SHUTDOWN);
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
     * @param \Error $thrown
     * @param bool $exit
     */
    public function exceptionHandler(\Throwable $thrown, $exit = true): void {

        // 处理方案
        // 记日志
        // 如果是非生产环境条件允许打印输出
        tris::ml($thrown)->exc();

        // 是否输出
        if (!rt::isProd() && $this->isPrintable($thrown)) {
            $this->printThrown($thrown);
        }

        if ($exit) {
            die(0);
        }
    }

    /**
     * @param \Throwable $thrown
     * @todo 这里用tris::du()不是最合适
     */
    protected function printThrown(\Throwable $thrown) {
        if ($thrown instanceof abort) {
            $context = $thrown->all();
            $thrown  = $thrown->getPrevious();
        } elseif ($thrown instanceof ErrorException) {
            $context = $thrown->getContext();
        } else {
            $context = [];
        }
        $sample     = new ExceptionSample('p', $thrown, $context);
        tris::du($sample->render(), "Ooooooooooooooooooops!!");
    }

    protected function isPrintable(\Throwable $thrown): bool {
        if ($this->config['print_exc']) {
            return true;
        }
        return false;
    }

}
