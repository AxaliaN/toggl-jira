<?php

use Monolog\Logger;

return [
    'monolog' => [
        'loggers' => [
            'AcsiErrorHandling\Logger' => [
                'name' => 'error',
                'handlers' => [
                    'default' => [
                        'name' => 'Monolog\Handler\StreamHandler',
                        'options' => [
                            'stream' => 'php://stdout',
                            'level' => Logger::DEBUG,
                        ],
                    ],
                ]
            ],
            'AcsiEventHandling\Logger' => [
                'name' => 'event',
                'handlers' => [
                    'default' => [
                        'name' => 'Monolog\Handler\StreamHandler',
                        'options' => [
                            'stream' => 'php://stdout',
                            'level' => Logger::DEBUG,
                        ],
                    ],
                ]
            ],
        ],
    ],
];
