<?php

namespace Combi\Core;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Psr\Log\LogLevel;
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

    public function __construct(string $channel, ?array $config = null) {
        $this->channel = $channel;

        if ($config) {
            $this->logger = $this->createLogger($config);
        } else {
            $this->logger = Logger\NullLogger::instance();
        }
    }

    public function getLogger(): MonoLogger {
        return $this->logger;
    }

    public function log($level, $message, array $context = []): void
    {
        $processors = $this->logger->getProcessors();
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

    private function createLogger(array $config): MonoLogger {
        $logger     = new MonoLogger($this->channel);

        foreach ($config['handlers'] as $handler_conf) {
            $formatter  = null;

            $handler = helper::make(helper::entityWithProcessor($handler_conf,
                function($name, $value) use (&$formatter, $config)
            {
                switch ($name) {
                    case 'file':
                        $value[0] != DIRECTORY_SEPARATOR
                            && $value = core::path('logs', $value);
                        break;

                    case 'level':
                        $value = self::LEVELS[$value];
                        break;

                    case 'formatter':
                        // 为了效率，这里在处理formatter之前检查配置并报错了
                        if (!isset($config['formatters'][$value])) {
                            throw abort::runtime(
                                'Logger formatter %name% is not defined')
                                    ->set('name', $value);
                        }
                        $formatter = $this->getFormatter($value,
                            $config['formatters']);
                        return null;

                    default:
                        break;
                }
                return $value;
            }));
            $formatter && $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
        }

        if (isset($config['processors'])) {
            foreach ($config['processors'] as $processor_conf) {
                $logger->pushProcessor(helper::instance($processor_conf));
            }
        }

        return $logger;
    }

    private function getFormatter(string $name, array $config): FormatterInterface {
        if (!isset($this->formatters[$name])) {
            $this->formatters[$name] = helper::make($config[$name]);
        }

        return $this->formatters[$name];
    }

}

