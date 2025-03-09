<?php

namespace App\Logging;

use App\Logging\OtelLogHandler;
use Monolog\Logger;
use OpenTelemetry\API\Logs\LoggerInterface;

class OtelLogger
{
    public function __invoke(array $config)
    {
        return new Logger('otel', [
            new OtelLogHandler(
                app(LoggerInterface::class),
                Logger::toMonologLevel($config['level'] ?? 'debug')
            )
        ]);
    }
}
