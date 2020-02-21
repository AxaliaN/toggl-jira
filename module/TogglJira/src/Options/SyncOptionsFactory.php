<?php
declare(strict_types=1);

namespace TogglJira\Options;

use DateTime;
use Exception;
use Interop\Container\ContainerInterface;
use RuntimeException;
use Zend\Config\Reader\Json;
use Zend\ServiceManager\Factory\FactoryInterface;

class SyncOptionsFactory implements FactoryInterface
{
    /**
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SyncOptions
    {
        $reader = new Json();
        $config = $reader->fromFile(__DIR__ . '/../../../../config.json');

        if (!isset($config['jiraUsername'], $config['jiraPassword'], $config['togglApiKey'], $config['jiraUrl']) || empty($config['jiraUsername']) || empty($config['jiraPassword']) || empty($config['togglApiKey']) || empty($config['jiraUrl'])) {
            throw new RuntimeException('Invalid config.json, please fill out everything except lastSync');
        }

        if (isset($config['lastSync']['date']) && isset($config['lastSync']['timezone'])) {
            $config['lastSync'] = new DateTime(
                $config['lastSync']['date'],
                new \DateTimeZone($config['lastSync']['timezone'])
            );
        }

        return new SyncOptions($config);
    }
}
