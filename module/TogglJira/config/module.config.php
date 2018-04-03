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
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'exception_handler' => [
        'template' => __DIR__ . '/../templates/error.notice.tpl',
    ],
];
