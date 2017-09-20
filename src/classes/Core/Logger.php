<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Runtime as rt
};

use Psr\Log\{
    LoggerInterface,
    LogLevel
};
use Monolog\Logger as MonoLogger;
use Monolog\Formatter\FormatterInterface;

/**
 */
class Logger extends \Psr\Log\AbstractLogger
{
    const LEVELS = [
        LogLevel::EMERGENCY => MonoLogger::EMERGENCY,
        LogLevel::ALERT     => MonoLogger::ALERT,
        LogLevel::CRITICAL  => MonoLogger::CRITICAL,
        LogLevel::ERROR     => MonoLogger::ERROR,
        LogLevel::WARNING   => MonoLogger::WARNING,
        LogLevel::NOTICE    => MonoLogger::NOTICE,
        LogLevel::INFO      => MonoLogger::INFO,
        LogLevel::DEBUG     => MonoLogger::DEBUG,
    ];

    /**
     *
     * @var string
     */
    private $channel;

    /**
     *
     * @var MonoLogger|NullLogger
     */
    private $logger = null;

    /**
     *
     * @var FormatterInterface[]
     */
    private $formatters = [];

    public function __construct(string $channel, ?LoggerInterface $logger = null) {
        $this->channel  = $channel;
        $this->logger   = $logger ?: Logger\NullLogger::instance();
    }

    public function getLogger(): LoggerInterface {
        return $this->logger;
    }

    public function log($level, $message, array $context = []): void
    {
        $processors = $this->logger instanceof MonoLogger
            ? $this->logger->getProcessors() : [];
        if (($processors[0] ?? null)
            instanceof Logger\RichMessageProcessor)
        {
            $this->logger->$level(...$this->prepare($message, $context));
        } else {
            $this->logger->$level($message, $context);
        }
    }

    private function prepare($message, array $context = []): array {
        if (!is_numeric($message) && !is_string($message)) {
            $context['__richmsg'] = $message;
            $message = '';
        }
        return [$message, $context];
    }

}

