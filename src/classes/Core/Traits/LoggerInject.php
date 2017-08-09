<?php

namespace Combi\Core\Traits;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Psr\Log\LoggerInterface;

/**
 *
 *
 * @author andares
 */
trait LoggerInject
{
    protected $_logger = null;

    public function __call(string $name, array $arguments) {
        if (isset(core\Logger::LEVELS[$name])) {
            return $this->getlogger()->$name(...$arguments);
        }
        return $this->_callCustom($name, $arguments);
    }

    public function log(string $level, $message, array $context = []): void {
        $this->getLogger()->$level($message, $context);
    }

    public function getLogger(): LoggerInterface {
        return $this->_logger ?: helper::logger();
    }

    public function setLogger(LoggerInterface $logger): void {
        $this->_logger = $logger;
    }

    protected function _callCustom(string $name, array $arguments) {
        throw abort::badMethodCall('method "%method%()" not exist.')
            ->set('method', $name);
    }
}