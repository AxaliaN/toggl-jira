<?php

use TogglJira\Handler\CommandHandler;

return [
    'version' => 'alpha',
    'console' => [
        'routes' => [
            [
                'name' => 'sync',
                'route' => '',
                'description' => 'Sync Toggl entries to Jira',
                'short_description' => 'Sync',
                'defaults' => [],
                'handler' => CommandHandler::class,
            ],
        ],
    ],
];
