<?php
declare(strict_types=1);

namespace TogglJiraTest\Options;

use TogglJira\Options\SyncOptions;
use TogglJira\Options\SyncOptionsFactory;
use TogglJiraTest\BaseContainerTestCase;

class SyncOptionsFactoryTest extends BaseContainerTestCase
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
            "lastSync": "2018-04-03T10:10:55+02:00",
            "jiraUrl": "https://acsi-jira.atlassian.net",
            "jiraUsername": "foo",
            "jiraPassword": "bar",
            "togglApiKey": "baz"
        }');

        $factory = new SyncOptionsFactory();
        $instance  = $factory->__invoke($this->getContainer(), SyncOptions::class);

        $this->assertInstanceOf(SyncOptions::class, $instance);
    }

    /**
     * @return void
     * @throws \Interop\Container\Exception\ContainerException
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid config.json, please fill out everything except lastSync
     */
    public function testInvokeThrowsException(): void
    {
        if (file_exists(__DIR__ . '/../../../../config.json')) {
            rename(__DIR__ . '/../../../../config.json', __DIR__ . '/../../../../config.json.bak');
        }

        file_put_contents(__DIR__ . '/../../../../config.json', '{
            "lastSync": "",
            "jiraUrl": "",
            "jiraUsername": "",
            "jiraPassword": "",
            "togglApiKey": ""
        }');

        $factory = new SyncOptionsFactory();
        $factory->__invoke($this->getContainer(), SyncOptions::class);
    }

    public function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::tearDown();

        if (file_exists(__DIR__ . '/../../../../config.json.bak')) {
            rename(__DIR__ . '/../../../../config.json.bak', __DIR__ . '/../../../../config.json');
        }
    }
}
