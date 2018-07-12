<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    'monolog' => [
        'loggers' => [
            'Logger' => [
                'name' => 'event',
                'handlers' => [
                    'default' => [
                        'name' => StreamHandler::class,
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
