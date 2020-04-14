<?php
declare(strict_types=1);

namespace TogglJiraTest\Options;

use PHPUnit\Framework\TestCase;
use TogglJira\Options\SyncOptions;

class SyncOptionsTest extends TestCase
{
    /**
     * @return void
     * @throws \Exception
     */
    public function testAccessors(): void
    {
        $data = [
            'lastSync' => new \DateTimeImmutable('2017-04-15T23:35:00+02:00'),
            'jiraEmail' => 'bar@foo.com',
            'jiraAccountId' => 'foo',
            'jiraApiKey' => 'bar',
            'togglApiKey' => 'foz',
            'jiraUrl' => 'http://www.example.com',
        ];

        $syncOptions = new SyncOptions($data);

        $this->assertEquals($data['lastSync'], $syncOptions->getLastSync());
        $this->assertEquals($data['jiraAccountId'], $syncOptions->getjiraAccountId());
        $this->assertEquals($data['jiraApiKey'], $syncOptions->getjiraApiKey());
        $this->assertEquals($data['togglApiKey'], $syncOptions->getTogglApiKey());
        $this->assertEquals($data['jiraUrl'], $syncOptions->getJiraUrl());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testToArray(): void
    {
        $data = [
            'lastSync' => new \DateTimeImmutable('2017-04-15T23:35:00+02:00'),
            'jiraEmail' => 'bar@foo.com',
            'jiraAccountId' => 'foo',
            'jiraApiKey' => 'bar',
            'togglApiKey' => 'foz',
            'jiraUrl' => 'http://www.example.com',
        ];

        $syncOptions = new SyncOptions($data);
        $this->assertEquals($data, $syncOptions->toArray());
    }
}
