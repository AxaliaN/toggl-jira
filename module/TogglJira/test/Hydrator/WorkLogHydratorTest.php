<?php
declare(strict_types=1);

namespace TogglJiraTest\Hydrator;

use PHPUnit\Framework\TestCase;
use TogglJira\Entity\WorkLogEntry;
use TogglJira\Hydrator\WorkLogHydrator;

class WorkLogHydratorTest extends TestCase
{
    /**
     * @return void
     * @throws \Exception
     */
    public function testHydrate(): void
    {
        $hydrator = new WorkLogHydrator();

        $data = [
            'issueID' => 'SLR-76',
            'timeSpent' => 666,
            'comment' => 'Soldier 76, reporting for duty',
            'spentOn' => '2017-04-15T23:35:00+02:00'
        ];

        $object = $hydrator->hydrate($data, new WorkLogEntry());

        $this->assertEquals($data['issueID'], $object->getIssueID());
        $this->assertEquals($data['timeSpent'], $object->getTimeSpent());
        $this->assertEquals($data['comment'], $object->getComment());
        $this->assertEquals($data['spentOn'], $object->getSpentOn()->format(DATE_ATOM));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testExtract(): void
    {
        $hydrator = new WorkLogHydrator();

        $data = [
            'issueID' => 'DVA-42',
            'timeSpent' => 9001,
            'comment' => 'Nerf this!',
            'spentOn' => new \DateTimeImmutable('2017-04-15T23:50:00+02:0')
        ];

        $object = new WorkLogEntry();
        $object->setIssueID($data['issueID']);
        $object->setTimeSpent($data['timeSpent']);
        $object->setComment($data['comment']);
        $object->setSpentOn($data['spentOn']);

        $expected = $hydrator->extract($object);

        $this->assertEquals($data, $expected);
    }
}
