<?php
declare(strict_types=1);

namespace TogglJira\Options;

use Interop\Container\ContainerInterface;
use Zend\Config\Reader\Json;
use Zend\ServiceManager\Factory\FactoryInterface;

class SyncOptionsFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SyncOptions
    {
        $reader = new Json();
        $config = $reader->fromFile(__DIR__ . '/../../../../config.json');

        if ((!isset($config['jiraUsername']) || empty($config['jiraUsername'])) ||
        (!isset($config['jiraPassword']) || empty($config['jiraPassword'])) ||
        (!isset($config['togglApiKey']) || empty($config['togglApiKey'])) ||
        (!isset($config['jiraUrl']) || empty($config['jiraUrl']))
        ) {
            throw new \RuntimeException('Invalid config.json, please fill out everything except lastSync');
        }

        $config['lastSync'] = new \DateTimeImmutable(
            $config['lastSync']['date'],
            new \DateTimeZone($config['lastSync']['timezone'])
        );

        return new SyncOptions($config);
    }
}
