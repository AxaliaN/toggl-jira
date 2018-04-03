<?php
declare(strict_types=1);

namespace TogglJira\Command;

use Interop\Container\ContainerInterface;
use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use Zend\Config\Writer\Json;
use Zend\ServiceManager\Factory\FactoryInterface;

class SyncCommandFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SyncCommand
    {
        /** @var SyncOptions $options */
        $syncOptions = $container->get(SyncOptions::class);
        $writer = new Json();
        
        return new SyncCommand($container->get(SyncService::class), $syncOptions, $writer);
    }
}
