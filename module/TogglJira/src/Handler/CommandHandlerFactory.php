<?php
declare(strict_types=1);

namespace TogglJira\Handler;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class CommandHandlerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CommandHandler
    {
        $commandHandler = new CommandHandler($container);

        $logger = $container->get('Logger');

        $commandHandler->setLogger($logger);

        return $commandHandler;
    }
}
