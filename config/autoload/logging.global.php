<?php

return [
    'monolog' => [
        'loggers' => [
            'AcsiEventHandling\Logger' => [
                'name' => 'event',
            ],
            'AcsiErrorHandling\Logger' => [
                'name' => 'error',
            ],
        ]
    ]
];
