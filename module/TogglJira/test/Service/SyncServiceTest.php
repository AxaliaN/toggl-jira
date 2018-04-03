<?php
declare(strict_types=1);

namespace TogglJiraTest\Service;

use AJT\Toggl\TogglClient;
use DateTimeImmutable;
use Mockery\Exception;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TogglJira\Entity\WorkLogEntry;
use TogglJira\Hydrator\WorkLogHydrator;
use TogglJira\Jira\Api;
use TogglJira\Service\SyncService;

class SyncServiceTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $apiMock;

    /**
     * @var MockInterface
     */
    private $togglClientMock;

    /**
     * @var MockInterface
     */
    private $hydratorMock;

    /**
     * @var MockInterface
     */
    private $loggerMock;

    /**
     * @var SyncService
     */
    private $service;

    /**
     * @return void
     */
    public function setUp(): void
    {
       \ Mockery::getConfiguration()->allowMockingNonExistentMethods(true);

        $this->apiMock = \Mockery::mock(Api::class);
        $this->togglClientMock = \Mockery::mock(TogglClient::class);
        $this->hydratorMock = \Mockery::mock(WorkLogHydrator::class);
        $this->loggerMock = \Mockery::mock(LoggerInterface::class);

        $this->service = new SyncService($this->apiMock, $this->togglClientMock, $this->hydratorMock, 'D-Va');

        $this->service->setLogger($this->loggerMock);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSync(): void
    {
        $dateTime = '2017-04-15T23:35:00+02:00';

        $timeEntries = [
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, reporting for duty',
                'at' => '2018-02-15'
            ],
            [
                'description' => 'DVA-76 D-Va',
                'duration' => 42,
                'comment' => 'Nerf this!',
                'at' => '2018-02-15'
            ],
        ];

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => $dateTime])
            ->andReturn($timeEntries);

        $workLogEntry = new WorkLogEntry();
        $workLogEntry->setIssueID('SLR-76');
        $workLogEntry->setSpentOn(new DateTimeImmutable($dateTime));
        $workLogEntry->setComment('SLR-76');
        $workLogEntry->setTimeSpent(76);

        $this->hydratorMock->shouldReceive('hydrate')->andReturn($workLogEntry);

        $this->apiMock->shouldReceive('addWorkLogEntry')->with(
            $workLogEntry->getIssueID(),
            $workLogEntry->getTimeSpent(),
            'D-Va',
            $workLogEntry->getComment(),
            $dateTime
        );

        $this->loggerMock
            ->shouldReceive('info')
            ->with("Found time entry for user story {$workLogEntry->getIssueID()}");

        $this->loggerMock
            ->shouldReceive('info')
            ->with("Added worklog entry for issue {$workLogEntry->getIssueID()}");

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($dateTime);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testExceptionOnTogglError(): void
    {
        $dateTime = '2017-04-15T23:35:00+02:00';

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => $dateTime])
            ->andThrow(\Exception::class, 'Nerf this!');

        $this->loggerMock->shouldReceive('error')
            ->with('Failed to get time entries from Toggl: Nerf this!', \Mockery::any());

        $this->service->sync($dateTime);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSyncWithInvalidTimeEntries(): void
    {
        $dateTime = '2017-04-15T23:35:00+02:00';

        $timeEntries = [
            [
                'description' => 'SLR76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, not reporting for duty',
                'at' => '2018-02-15'
            ],
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => -1,
                'comment' => 'Soldier 76, not reporting for duty',
                'at' => '2018-02-15'
            ],
        ];

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => $dateTime])
            ->andReturn($timeEntries);

        $this->loggerMock->shouldReceive('warning'
        )->with('Could not parse issue string, cannot link to Jira');

        $this->loggerMock->shouldReceive('info')
            ->with('0 seconds, or timer still running for SLR-76, skipping');

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($dateTime);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSyncJiraException(): void
    {
        $dateTime = '2017-04-15T23:35:00+02:00';

        $timeEntries = [
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, reporting for duty',
                'at' => '2018-02-15'
            ],
            [
                'description' => 'DVA-76 D-Va',
                'duration' => 42,
                'comment' => 'Nerf this!',
                'at' => '2018-02-15'
            ],
        ];

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => $dateTime])
            ->andReturn($timeEntries);

        $workLogEntry = new WorkLogEntry();
        $workLogEntry->setIssueID('SLR-76');
        $workLogEntry->setSpentOn(new DateTimeImmutable($dateTime));
        $workLogEntry->setComment('SLR-76');
        $workLogEntry->setTimeSpent(76);

        $this->hydratorMock->shouldReceive('hydrate')->andReturn($workLogEntry);

        $this->apiMock->shouldReceive('addWorkLogEntry')->with(
            $workLogEntry->getIssueID(),
            $workLogEntry->getTimeSpent(),
            'D-Va',
            $workLogEntry->getComment(),
            $dateTime
        )->andThrow(\Exception::class, 'Nerf this!');

        $this->loggerMock
            ->shouldReceive('info')
            ->with("Found time entry for user story {$workLogEntry->getIssueID()}");

        $this->loggerMock
            ->shouldReceive('error')
            ->with("Could not add worklog entry: Nerf this!", \Mockery::any());

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($dateTime);
    }

    public function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
    }
}