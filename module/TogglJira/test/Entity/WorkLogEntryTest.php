<?php
declare(strict_types=1);

namespace TogglJiraTest\Entity;

use PHPUnit\Framework\TestCase;
use TogglJira\Entity\WorkLogEntry;

class WorkLogEntryTest extends TestCase
{
    /**
     * @var WorkLogEntry
     */
    private $entity;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->entity = new WorkLogEntry();
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testAccessors(): void
    {
        $data = [
            'issueID' => 'TST-01',
            'timeSpent' => 666,
            'comment' => 'This is a test',
            'spentOn' => new \DateTimeImmutable()
        ];

        $this->entity->setIssueID($data['issueID']);
        $this->entity->setTimeSpent($data['timeSpent']);
        $this->entity->setComment($data['comment']);
        $this->entity->setSpentOn($data['spentOn']);

        $this->assertEquals($data['issueID'], $this->entity->getIssueID());
        $this->assertEquals($data['timeSpent'], $this->entity->getTimeSpent());
        $this->assertEquals($data['comment'], $this->entity->getComment());
        $this->assertEquals($data['spentOn'], $this->entity->getSpentOn());
    }
}
