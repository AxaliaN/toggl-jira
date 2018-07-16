<?php
declare(strict_types=1);

namespace TogglJiraTest\Options;

use TogglJira\Options\SyncOptions;
use TogglJira\Options\SyncOptionsFactory;
use TogglJiraTest\BaseContainerTest;

class SyncOptionsFactoryTest extends BaseContainerTest
{
    /**
     * @return void
     * @throws \Interop\Container\Exception\ContainerException
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

    public function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::tearDown();

        \unlink(__DIR__ . '/../../../../config.json');

        if (\file_exists(__DIR__ . '/../../../../config.json.bak')) {
            \rename(__DIR__ . '/../../../../config.json.bak', __DIR__ . '/../../../../config.json');
        }
    }
}
