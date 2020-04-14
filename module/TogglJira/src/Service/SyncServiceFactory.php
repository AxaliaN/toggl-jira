<?php
declare(strict_types=1);

namespace TogglJira\Service;

use AJT\Toggl\TogglClient;
use chobie\Jira\Api\Authentication\Basic;
use Interop\Container\ContainerInterface;
use TogglJira\Hydrator\WorkLogHydrator;
use TogglJira\Jira\Api;
use TogglJira\Options\SyncOptions;
use Zend\ServiceManager\Factory\FactoryInterface;

class SyncServiceFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SyncService
    {
        /** @var SyncOptions $syncOptions */
        $syncOptions = $container->get(SyncOptions::class);

        $api = new Api(
            $syncOptions->getJiraUrl(),
            new Basic($syncOptions->getjiraEmail(), $syncOptions->getjiraApiKey())
        );

        $togglClient = TogglClient::factory(['api_key' => $syncOptions->getTogglApiKey(), 'apiVersion' => 'v8']);
        $logger = $container->get('Logger');

        $service = new SyncService(
            $api,
            $togglClient,
            new WorkLogHydrator(),
            $syncOptions->getjiraAccountId()
        );
        $service->setLogger($logger);

        return $service;
    }
}
