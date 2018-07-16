<?php
declare(strict_types=1);

namespace TogglJiraTest\Service;

use AJT\Toggl\TogglClient;
use DateTime;
use GuzzleHttp\Command\Result;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
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
        $startDate = new \DateTime('2017-04-15');
        $endDate = new \DateTime('2017-04-16');

        $timeEntries15th = [
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, reporting for duty',
                'start' => '2018-02-15'
            ],
            [
                'description' => 'DVA-76 D-Va',
                'duration' => 42,
                'comment' => 'Nerf this!',
                'start' => '2018-02-15'
            ],
        ];

        $timeEntries16th = [];

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => '2017-04-15T00:00:00+00:00', 'end_date' => '2017-04-15T23:59:59+00:00'])
            ->andReturn(new Result($timeEntries15th));

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => '2017-04-16T00:00:00+00:00', 'end_date' => '2017-04-16T23:59:59+00:00'])
            ->andReturn(new Result($timeEntries16th));

        $workLogEntry = new WorkLogEntry();
        $workLogEntry->setIssueID('SLR-76');
        $workLogEntry->setSpentOn(new \DateTimeImmutable('2018-02-15'));
        $workLogEntry->setComment('SLR-76');
        $workLogEntry->setTimeSpent(76);

        $this->hydratorMock->shouldReceive('hydrate')->andReturn($workLogEntry);

        $user = [
            'accountId' => 'D-Va'
        ];

        $this->apiMock->shouldReceive('getUser')->andReturn($user);

        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('getResult')->once();
        $this->apiMock->shouldReceive('addWorkLogEntry')->with(
            $workLogEntry->getIssueID(),
            $workLogEntry->getTimeSpent() * 2,
            'D-Va',
            $workLogEntry->getComment() . "\n" . $workLogEntry->getComment(),
            $workLogEntry->getSpentOn()->format('Y-m-d\TH:i:s.vO'),
            false
        )->andReturn($result);

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Found time entry for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2018-02-15', 'timeSpent' => '0.02 hours']
            );

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Added time spent for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2018-02-15', 'timeSpent' => '0.04 hours']
            );

        $this->loggerMock
            ->shouldReceive('info')
            ->with("Added time spent for issue {$workLogEntry->getIssueID()}");

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Saved worklog entry',
                ['issueID' => 'SLR-76', 'spentOn' => '2018-02-15', 'timeSpent' => '0.04 hours']
            );

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($startDate, $endDate, false);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testExceptionOnTogglError(): void
    {
        $startTime = new DateTime('2017-04-15');
        $endDate = new DateTime('2017-04-16');

        $user = [
            'accountId' => 'D-Va'
        ];

        $this->apiMock->shouldReceive('getUser')->andReturn($user);

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => '2017-04-15T00:00:00+00:00', 'end_date' => '2017-04-15T23:59:59+00:00'])
            ->andThrow(\Exception::class, 'Nerf this!');

        $this->loggerMock->shouldReceive('error')
            ->with('Failed to get time entries from Toggl', \Mockery::any());

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($startTime, $endDate, false);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSyncWithInvalidTimeEntries(): void
    {
        $startDate = new DateTime('2017-04-15');
        $endDate = new DateTime('2017-04-15');

        $timeEntries = [
            [
                'description' => 'SLR76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, not reporting for duty',
                'start' => '2018-02-15'
            ],
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => -1,
                'comment' => 'Soldier 76, not reporting for duty',
                'start' => '2018-02-15'
            ],
        ];

        $result = new Result($timeEntries);

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => '2017-04-15T00:00:00+00:00', 'end_date' => '2017-04-15T23:59:59+00:00'])
            ->andReturn($result);

        $user = [
            'accountId' => 'D-Va'
        ];

        $this->apiMock->shouldReceive('getUser')->andReturn($user);

        $this->loggerMock->shouldReceive('warning')
            ->with('Could not parse issue string, cannot link to Jira');

        $this->loggerMock->shouldReceive('info')
            ->with('0 seconds, or timer still running, skipping', ['issueID' => 'SLR-76']);

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($startDate, $endDate, false);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSyncJiraException(): void
    {
        $startDate = new DateTime('2017-04-15');
        $endDate = new DateTime('2017-04-15');

        $user = [
            'accountId' => 'D-Va'
        ];

        $this->apiMock->shouldReceive('getUser')->andReturn($user);

        $timeEntries = [
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, reporting for duty',
                'start' => '2018-02-15'
            ],
            [
                'description' => 'DVA-76 D-Va',
                'duration' => 42,
                'comment' => 'Nerf this!',
                'start' => '2018-02-15'
            ],
        ];

        $result = new Result($timeEntries);
        
        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with(['start_date' => '2017-04-15T00:00:00+00:00', 'end_date' => '2017-04-15T23:59:59+00:00'])
            ->andReturn($result);

        $workLogEntry = new WorkLogEntry();
        $workLogEntry->setIssueID('SLR-76');
        $workLogEntry->setSpentOn(new \DateTimeImmutable('2018-02-15'));
        $workLogEntry->setComment('SLR-76');
        $workLogEntry->setTimeSpent(76);

        $this->hydratorMock->shouldReceive('hydrate')->andReturn($workLogEntry);

        $this->apiMock->shouldReceive('addWorkLogEntry')->with(
            $workLogEntry->getIssueID(),
            $workLogEntry->getTimeSpent() * 2,
            'D-Va',
            $workLogEntry->getComment(),
            $workLogEntry->getSpentOn()->format('Y-m-d\TH:i:s.vO')
        )->andThrow(\Exception::class, 'Nerf this!');

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Found time entry for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2018-02-15', 'timeSpent' => '0.02 hours']
            );

        $this->loggerMock
            ->shouldReceive('error')
            ->with('Could not add worklog entry', \Mockery::any());

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Added time spent for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2018-02-15', 'timeSpent' => '0.04 hours']
            );

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($startDate, $endDate, false);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage User with username D-Va not found
     * @return void
     * @throws \Exception
     */
    public function testExceptionThrownOnUserNotFound(): void
    {
        $startDate = new DateTime('2017-04-15');
        $endDate = new DateTime('2017-04-15');
        $this->apiMock->shouldReceive('getUser')->andReturn([]);

        $this->service->sync($startDate, $endDate, false);
    }

    public function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
    }
}
