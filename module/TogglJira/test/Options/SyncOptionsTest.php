<?php
declare(strict_types=1);

namespace TogglJiraTest\Options;

use PHPUnit\Framework\TestCase;
use TogglJira\Options\SyncOptions;

class SyncOptionsTest extends TestCase
{
    public function testAccessors(): void
    {
        $data = [
            'lastSync' => new \DateTime('2017-04-15T23:35:00+02:00'),
            'jiraUsername' => 'foo',
            'jiraLoginUsername' => 'foo@example.com',
            'jiraPassword' => 'bar',
            'togglApiKey' => 'foz',
            'jiraUrl' => 'http://www.example.com',
            'notifyUsers' => true,
        ];

        $syncOptions = new SyncOptions($data);

        $this->assertEquals($data['lastSync'], $syncOptions->getLastSync());
        $this->assertEquals($data['jiraUsername'], $syncOptions->getJiraUsername());
        $this->assertEquals($data['jiraLoginUsername'], $syncOptions->getJiraLoginUsername());
        $this->assertEquals($data['jiraPassword'], $syncOptions->getJiraPassword());
        $this->assertEquals($data['togglApiKey'], $syncOptions->getTogglApiKey());
        $this->assertEquals($data['jiraUrl'], $syncOptions->getJiraUrl());
        $this->assertTrue($syncOptions->isNotifyUsers());
    }

    public function testToArray(): void
    {
        $data = [
            'lastSync' => new \DateTime('2017-04-15T23:35:00+02:00'),
            'jiraUsername' => 'foo',
            'jiraLoginUsername' => 'foo@example.com',
            'jiraPassword' => 'bar',
            'togglApiKey' => 'foz',
            'jiraUrl' => 'http://www.example.com',
            'fillIssueID' => 'FOO-01',
            'fillIssueComment' => 'Support',
            'notifyUsers' => true,
        ];

        $syncOptions = new SyncOptions($data);
        $this->assertEquals($data, $syncOptions->toArray());
    }
}
