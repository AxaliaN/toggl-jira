<?php
declare(strict_types=1);

namespace TogglJira\Options;

use Interop\Container\ContainerInterface;
use TogglJira\Utils\ConfigKeyValidator;
use Zend\ServiceManager\Factory\FactoryInterface;

class SyncOptionsFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SyncOptions
    {
        $config = json_decode(file_get_contents(__DIR__ . '/../../../../config.json'), true);

        ConfigKeyValidator::validateConfig(
            ['lastSync', 'jiraUsername', 'jiraPassword', 'togglApiKey', 'jiraUrl'],
            $config
        );

        if (empty($config['jiraUsername']) || empty($config['jiraPassword']) || empty($config['togglApiKey'])) {
            throw new \RuntimeException('Invalid config.json, please fill out everything except lastSync');
        }

        return new SyncOptions($config);
    }
}
