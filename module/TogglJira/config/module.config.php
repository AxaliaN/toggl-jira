<?php
declare(strict_types=1);

namespace TogglJira;

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
