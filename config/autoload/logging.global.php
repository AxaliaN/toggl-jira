<?php

use Monolog\Logger;

return [
    'monolog' => [
        'loggers' => [
            'Logger' => [
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
