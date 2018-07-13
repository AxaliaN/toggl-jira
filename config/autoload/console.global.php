<?php

use TogglJira\Handler\CommandHandler;

return [
    'version' => 'beta',
    'console' => [
        'routes' => [
            [
                'name' => 'sync',
                'route' => '[--startDate=] [--endDate=] [--overwrite=]',
                'description' => 'Sync Toggl entries to Jira',
                'short_description' => 'Sync',
                'defaults' => [],
                'handler' => CommandHandler::class,
            ],
        ],
    ],
];
