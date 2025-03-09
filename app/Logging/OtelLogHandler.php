<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;

class OtelLogHandler extends AbstractProcessingHandler
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, $level = \Monolog\Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->logger = $logger;
    }

    protected function write(array|\Monolog\LogRecord $record): void
    {
        $logRecord = (new LogRecord())
            ->setBody($record['message'])
            ->setAttributes($record['context'])
            ->setSeverityNumber($this->toOtelSeverity($record['level']))
            ->setTimestamp((int) ($record['datetime']->format('U.u') * 1e6));

        $this->logger->emit($logRecord);
    }

    private function toOtelSeverity(int $monologLevel): int
    {
        return match (true) {
            $monologLevel >= \Monolog\Logger::ERROR => 17, // ERROR
            $monologLevel >= \Monolog\Logger::WARNING => 13, // WARN
            $monologLevel >= \Monolog\Logger::INFO => 9, // INFO
            default => 5, // DEBUG
        };
    }
}
