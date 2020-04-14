<?php
declare(strict_types=1);

namespace TogglJiraTest\Service;

use AJT\Toggl\TogglClient;
use DateTimeImmutable;
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
     * @var MockInterface
     */
    private $togglClientMock;

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

        $this->service = new SyncService(
            $this->apiMock,
            $this->togglClientMock,
            $this->hydratorMock,
            'D-Va',
            'D-Va@bla.com'
        );

        $this->service->setLogger($this->loggerMock);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSync(): void
    {
        $startDate = new DateTimeImmutable('2017-04-15T23:35:00.000+0200');
        $endDate = new DateTimeImmutable('2017-04-16T23:35:00.000+0200');

        $timeEntries = [
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, reporting for duty',
                'start' => '2018-02-15',
            ],
            [
                'description' => 'DVA-76 D-Va',
                'duration' => 42,
                'comment' => 'Nerf this!',
                'start' => '2018-02-15',
            ],
        ];

        $result = new Result($timeEntries);

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with([
                'start_date' => $startDate->format(DATE_ATOM),
                'end_date' => $endDate->format(DATE_ATOM),
            ])
            ->andReturn($result);

        $workLogEntry = new WorkLogEntry();
        $workLogEntry->setIssueID('SLR-76');
        $workLogEntry->setSpentOn($startDate);
        $workLogEntry->setComment('SLR-76');
        $workLogEntry->setTimeSpent(76);

        $this->hydratorMock->shouldReceive('hydrate')->andReturn($workLogEntry);

        $user = [
            'accountId' => 'D-Va',
        ];

        $this->apiMock->shouldReceive('getUser')->andReturn($user);

        $this->apiMock->shouldReceive('addWorkLogEntry')->with(
            $workLogEntry->getIssueID(),
            $workLogEntry->getTimeSpent() * 2,
            'D-Va',
            $workLogEntry->getComment() . "\n" . $workLogEntry->getComment(),
            $startDate->format('Y-m-d\TH:i:s.vO'),
            false
        );

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Found time entry for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2017-04-15', 'timeSpent' => '0.02 hours']
            );

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Added time spent for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2017-04-15', 'timeSpent' => '0.04 hours']
            );

        $this->loggerMock
            ->shouldReceive('info')
            ->with("Added time spent for issue {$workLogEntry->getIssueID()}");

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Saved worklog entry',
                ['issueID' => 'SLR-76', 'spentOn' => '2017-04-15', 'timeSpent' => '0.04 hours']
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
        $dateTime = new DateTimeImmutable('2017-04-15T23:35:00+02:00');
        $endDate = new DateTimeImmutable('2017-04-16T23:35:00+02:00');

        $user = [
            'accountId' => 'D-Va',
        ];

        $this->apiMock->shouldReceive('getUser')->andReturn($user);

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with([
                'start_date' => $dateTime->format(DATE_ATOM),
                'end_date' => $endDate->format(DATE_ATOM),
            ])
            ->andThrow(\Exception::class, 'Nerf this!');

        $this->loggerMock->shouldReceive('error')
            ->with('Failed to get time entries from Toggl', \Mockery::any());

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($dateTime, $endDate, false);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSyncWithInvalidTimeEntries(): void
    {
        $startDate = new DateTimeImmutable('2017-04-15T23:35:00+02:00');
        $endDate = new DateTimeImmutable('2017-04-16T23:35:00+02:00');

        $timeEntries = [
            [
                'description' => 'SLR76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, not reporting for duty',
                'start' => '2018-02-15',
            ],
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => -1,
                'comment' => 'Soldier 76, not reporting for duty',
                'start' => '2018-02-15',
            ],
        ];

        $result = new Result($timeEntries);

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with([
                'start_date' => $startDate->format(DATE_ATOM),
                'end_date' => $endDate->format(DATE_ATOM),
            ])
            ->andReturn($result);

        $user = [
            'accountId' => 'D-Va',
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
        $startDate = new DateTimeImmutable('2017-04-15T23:35:00.000+0200');
        $endDate = new DateTimeImmutable('2017-04-16T23:35:00.000+0200');

        $user = [
            'accountId' => 'D-Va',
        ];

        $this->apiMock->shouldReceive('getUser')->andReturn($user);

        $timeEntries = [
            [
                'description' => 'SLR-76 Soldier 76',
                'duration' => 76,
                'comment' => 'Soldier 76, reporting for duty',
                'start' => '2018-02-15',
            ],
            [
                'description' => 'DVA-76 D-Va',
                'duration' => 42,
                'comment' => 'Nerf this!',
                'start' => '2018-02-15',
            ],
        ];

        $result = new Result($timeEntries);

        $this->togglClientMock->shouldReceive('getTimeEntries')
            ->with([
                'start_date' => $startDate->format(DATE_ATOM),
                'end_date' => $endDate->format(DATE_ATOM),
            ])
            ->andReturn($result);

        $workLogEntry = new WorkLogEntry();
        $workLogEntry->setIssueID('SLR-76');
        $workLogEntry->setSpentOn($startDate);
        $workLogEntry->setComment('SLR-76');
        $workLogEntry->setTimeSpent(76);

        $this->hydratorMock->shouldReceive('hydrate')->andReturn($workLogEntry);

        $this->apiMock->shouldReceive('addWorkLogEntry')->with(
            $workLogEntry->getIssueID(),
            $workLogEntry->getTimeSpent() * 2,
            'D-Va',
            $workLogEntry->getComment(),
            $startDate
        )->andThrow(\Exception::class, 'Nerf this!');

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Found time entry for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2017-04-15', 'timeSpent' => '0.02 hours']
            );

        $this->loggerMock
            ->shouldReceive('error')
            ->with('Could not add worklog entry', \Mockery::any());

        $this->loggerMock
            ->shouldReceive('info')
            ->with(
                'Added time spent for issue',
                ['issueID' => 'SLR-76', 'spentOn' => '2017-04-15', 'timeSpent' => '0.04 hours']
            );

        $this->loggerMock->shouldReceive('info')->with('All done for today, time to go home!');

        $this->service->sync($startDate, $endDate, false);
    }

    /**
     * @expectedException RuntimeException
     * @return void
     * @throws \Exception
     */
    public function testExceptionThrownOnUserNotFound(): void
    {
        $startDate = new DateTimeImmutable('2017-04-15T23:35:00.000+0200');
        $endDate = new DateTimeImmutable('2017-04-16T23:35:00.000+0200');
        $this->apiMock->shouldReceive('getUser')->andReturn(['errorMessages' => ['error']]);

        $this->service->sync($startDate, $endDate, false);
    }

    public function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
    }
}
