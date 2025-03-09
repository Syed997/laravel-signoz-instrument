<?php

return [
    'service_name' => 'PHP_Integration_Signoz',
    'exporter' => [
        'type' => 'otlp',
        'endpoint' => getenv('OTLP_ENDPOINT') ?: 'http://localhost:4317',
    ],
];
