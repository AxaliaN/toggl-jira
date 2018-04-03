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
        /** @var SyncOptions $options */
        $syncOptions = $container->get(SyncOptions::class);

        $api = new Api(
            $syncOptions->getJiraUrl(),
            new Basic($syncOptions->getJiraUsername(), $syncOptions->getJiraPassword())
        );

        $togglClient = TogglClient::factory(['api_key' => $syncOptions->getTogglApiKey(), 'apiVersion' => 'v8']);

        $logger = $container->get('Logger');

        $service = new SyncService($api, $togglClient, new WorkLogHydrator(), '5abe06ff5dcb0a34cbf50d23');
        $service->setLogger($logger);

        return $service;
    }
}
