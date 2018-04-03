<?php
namespace TogglJira;

use Monolog\Logger;
use TogglJira\Command\SyncCommand;
use TogglJira\Command\SyncCommandFactory;
use TogglJira\Factory\RequestFactory;
use TogglJira\Handler\CommandHandler;
use TogglJira\Handler\CommandHandlerFactory;
use TogglJira\Options\SyncOptions;
use TogglJira\Options\SyncOptionsFactory;
use TogglJira\Service\SyncService;
use TogglJira\Service\SyncServiceFactory;
use Zend\Console\Request;

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
    'monolog' => [
        'loggers' => [
            'AcsiErrorHandling\Logger' => [
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
            'AcsiEventHandling\Logger' => [
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
        ],
    ],
    'service_manager' => [
        'factories' => [
            Request::class => RequestFactory::class,
            CommandHandler::class => CommandHandlerFactory::class,
            SyncCommand::class => SyncCommandFactory::class,
            SyncOptions::class => SyncOptionsFactory::class,
            SyncService::class => SyncServiceFactory::class,
        ],
        'aliases' => [
            'sync' => SyncCommand::class,
        ],
    ],
    'exception_handler' => [
        'template' => __DIR__ . '/../templates/error.notice.tpl',
    ],
];
