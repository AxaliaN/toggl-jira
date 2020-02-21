<?php
declare(strict_types=1);

namespace TogglJiraTest\Options;

use Exception;
use Interop\Container\Exception\ContainerException;
use TogglJira\Options\SyncOptions;
use TogglJira\Options\SyncOptionsFactory;
use TogglJiraTest\BaseContainerTest;

use function file_exists;
use function rename;
use function unlink;

class SyncOptionsFactoryTest extends BaseContainerTest
{
    /**
     * @throws Exception
     * @throws ContainerException
     */
    public function testInvoke(): void
    {
        if (file_exists(__DIR__ . '/../../../../config.json')) {
            rename(__DIR__ . '/../../../../config.json', __DIR__ . '/../../../../config.json.bak');
        }

        file_put_contents(__DIR__ . '/../../../../config.json', '{
            "lastSync": {
                "date": "2018-04-03T10:10:55+02:00",
                "timezone": "Europe/Amsterdam"
            },
            "jiraUrl": "https://jira.atlassian.net",
            "jiraUsername": "foo",
            "jiraPassword": "bar",
            "togglApiKey": "baz"
        }');

        $factory = new SyncOptionsFactory();
        $instance  = $factory->__invoke($this->getContainer(), SyncOptions::class);

        $this->assertInstanceOf(SyncOptions::class, $instance);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unlink(__DIR__ . '/../../../../config.json');

        if (file_exists(__DIR__ . '/../../../../config.json.bak')) {
            rename(__DIR__ . '/../../../../config.json.bak', __DIR__ . '/../../../../config.json');
        }
    }
}
