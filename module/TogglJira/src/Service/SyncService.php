<?php
declare(strict_types=1);

namespace TogglJira\Service;

use AJT\Toggl\TogglClient;
use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use TogglJira\Entity\WorkLogEntry;
use TogglJira\Hydrator\WorkLogHydrator;
use TogglJira\Jira\Api;

class SyncService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const REQUIRED_TIME_SPENT = 28800;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var TogglClient
     */
    private $togglClient;

    /**
     * @var WorkLogHydrator
     */
    private $workLogHydrator;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $fillIssueID;

    /**
     * @var string
     */
    private $fillIssueComment;

    /**
     * @var bool
     */
    private $notifyUsers;

    /**
     * @param Api             $api
     * @param GuzzleClient    $togglClient
     * @param WorkLogHydrator $workLogHydrator
     * @param string          $username
     * @param string|null     $fillIssueID
     * @param string          $fillIssueComment
     * @param bool            $notifyUsers
     */
    public function __construct(
        Api $api,
        GuzzleClient $togglClient,
        WorkLogHydrator $workLogHydrator,
        string $username,
        string $fillIssueID = null,
        string $fillIssueComment = '',
        bool $notifyUsers = true
    ) {
        $this->api = $api;
        $this->togglClient = $togglClient;
        $this->workLogHydrator = $workLogHydrator;
        $this->username = $username;
        $this->fillIssueID = $fillIssueID;
        $this->fillIssueComment = $fillIssueComment;
        $this->notifyUsers = $notifyUsers;
    }

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @param bool $overwrite
     * @return void
     * @throws Exception
     */
    public function sync(DateTimeInterface $startDate, DateTimeInterface $endDate, bool $overwrite): void
    {
        // Make sure we always start and end at 0:00. We only sync per day.
        $startDate = new DateTime($startDate->format('Y-m-d'));
        $endDate = new DateTime($endDate->format('Y-m-d'));

        $user = $this->api->getUser($this->username);

        if (!isset($user['accountId'])) {
            throw new RuntimeException("User with username {$this->username} not found");
        }

        // Iterate over each day and process all time entries.
        while ($startDate <= $endDate) {
            // Fetch time entries once per day, use the startDate +1 day at 0:00:00, to make sure we cover the full day
            // in the iteration.
            $clonedStartDate = clone $startDate;
            $timeEntries = $this->getTimeEntries(
                $startDate,
                $clonedStartDate->add(new DateInterval('PT23H59M59S'))
            );

            if ($timeEntries === null) {
                break;
            }

            $startDate->modify('+1 day');

            if (empty($timeEntries)) {
                continue;
            }

            $workLogs = $this->parseTimeEntries($timeEntries);

            // Don't fill the current day, since the day might not be over yet
            // Otherwise, use the filler issue to add the remaining time in order to have the full day filled
            // Also, only for week days
            if ($this->fillIssueID &&
                $clonedStartDate->format('d-m-Y') !== (new DateTime())->format('d-m-Y') &&
                $clonedStartDate->format('N') <= 5
            ) {
                $workLogs = $this->fillTimeToFull($workLogs, $clonedStartDate);
            }

            $this->addWorkLogsToApi($workLogs, $user, $overwrite, $this->notifyUsers);
        }

        $this->logger->info('All done for today, time to go home!');
    }

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @return array|null
     */
    private function getTimeEntries(DateTimeInterface $startDate, DateTimeInterface $endDate): ?array
    {
        try {
            /** @var array $timeEntries */
            return $this->togglClient->getTimeEntries(
                [
                    'start_date' => $startDate->format(DATE_ATOM),
                    'end_date' => $endDate->format(DATE_ATOM),
                ]
            )->toArray();
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to get time entries from Toggl',
                ['exception' => $e]
            );

            return null;
        }
    }

    /**
     * @param array $timeEntries
     * @return array
     * @throws Exception
     */
    private function parseTimeEntries(array $timeEntries): array
    {
        $workLogEntries = [];

        foreach ($timeEntries as $timeEntry) {
            $workLogEntry = $this->parseTimeEntry($timeEntry);

            if (!$workLogEntry) {
                continue;
            }

            $existingKey = md5($workLogEntry->getIssueID() . '-' . $workLogEntry->getSpentOn()->format('Y-m-d'));

            if (isset($workLogEntries[$existingKey])) {
                $this->addTimeToExistingTimeEntry($workLogEntries[$existingKey], $workLogEntry);
                continue;
            }

            $workLogEntries[$existingKey] = $workLogEntry;

            $this->logger->info('Found time entry for issue', [
                'issueID' => $workLogEntry->getIssueID(),
                'spentOn' => $workLogEntry->getSpentOn()->format('Y-m-d'),
                'timeSpent' => round($workLogEntry->getTimeSpent() / 60 / 60, 2) . ' hours',
            ]);
        }

        return $workLogEntries;
    }

    /**
     * @param array $timeEntry
     * @return WorkLogEntry|null
     * @throws Exception
     */
    private function parseTimeEntry(array $timeEntry): ?WorkLogEntry
    {
        $data = [
            'issueID' => explode(' ', $timeEntry['description'])[0],
            'timeSpent' => $timeEntry['duration'],
            'comment' => $timeEntry['description'],
            'spentOn' => $timeEntry['start']
        ];

        if (strpos($data['issueID'], '-') === false) {
            $this->logger->warning('Could not parse issue string, cannot link to Jira');
            return null;
        }

        if ($data['timeSpent'] < 0) {
            $this->logger->info('0 seconds, or timer still running, skipping', [
                'issueID' => $data['issueID']
            ]);
            return null;
        }

        return $this->workLogHydrator->hydrate($data, new WorkLogEntry());
    }

    /**
     * @param $existingWorkLog
     * @param $newWorkLog
     * @return WorkLogEntry
     */
    private function addTimeToExistingTimeEntry(WorkLogEntry $existingWorkLog, WorkLogEntry $newWorkLog): WorkLogEntry
    {
        $timeSpent = $existingWorkLog->getTimeSpent();
        $timeSpent += $newWorkLog->getTimeSpent();

        $existingWorkLog->setTimeSpent($timeSpent);

        if (!preg_match("/{$existingWorkLog->getComment()}/", $existingWorkLog->getComment())) {
            $existingWorkLog->setComment($existingWorkLog->getComment() . "\n" . $newWorkLog->getComment());
        }

        $this->logger->info('Added time spent for issue', [
            'issueID' => $newWorkLog->getIssueID(),
            'spentOn' => $newWorkLog->getSpentOn()->format('Y-m-d'),
            'timeSpent' => round($newWorkLog->getTimeSpent() / 60 / 60, 2) . ' hours',
        ]);

        return $existingWorkLog;
    }

    /**
     * @param array $workLogEntries
     * @param array $user
     * @param bool  $overwrite
     * @param bool  $notifyUsers
     *
     * @return void
     */
    private function addWorkLogsToApi(
        array $workLogEntries,
        array $user,
        bool $overwrite,
        bool $notifyUsers = true
    ): void {
        /** @var WorkLogEntry $workLogEntry */
        foreach ($workLogEntries as $workLogEntry) {
            try {
                $result = $this->api->addWorkLogEntry(
                    $workLogEntry->getIssueID(),
                    $workLogEntry->getTimeSpent(),
                    $user['accountId'],
                    $workLogEntry->getComment(),
                    $workLogEntry->getSpentOn()->format('Y-m-d\TH:i:s.vO'),
                    $overwrite,
                    $notifyUsers
                );

                if (isset($result->getResult()['errorMessages']) && \count($result->getResult()['errorMessages']) > 0) {
                    $this->logger->error(implode("\n", $result->getResult()['errorMessages']), [
                        'issueID' => $workLogEntry->getIssueID()
                    ]);
                }

                $this->logger->info('Saved worklog entry', [
                    'issueID' => $workLogEntry->getIssueID(),
                    'spentOn' => $workLogEntry->getSpentOn()->format('Y-m-d'),
                    'timeSpent' => round($workLogEntry->getTimeSpent() / 60 / 60, 2) . ' hours',
                ]);
            } catch (Exception $e) {
                $this->logger->error('Could not add worklog entry', ['exception' => $e]);
            }
        }
    }

    /**
     * @param array $workLogEntries
     * @return array
     */
    private function fillTimeToFull(array $workLogEntries, DateTime $processDate): array
    {
        $timeSpent = 0;

        /** @var WorkLogEntry $workLogEntry */
        foreach ($workLogEntries as $workLogEntry) {
            if ($workLogEntry->getIssueID() == $this->fillIssueID) {
                $fillIssue = $workLogEntry;
            }

            $timeSpent += $workLogEntry->getTimeSpent();
        }

        if ($timeSpent >= self::REQUIRED_TIME_SPENT) {
            return $workLogEntries;
        }

        $fillTime = self::REQUIRED_TIME_SPENT - $timeSpent + 60;

        if (!isset($fillIssue)) {
            $fillIssue = new WorkLogEntry();
            $fillIssue->setIssueID($this->fillIssueID);
            $fillIssue->setComment($this->fillIssueComment);
            $fillIssue->setSpentOn($processDate);
            $fillIssue->setTimeSpent($fillTime);

            $workLogEntries[] = $fillIssue;
        } else {
            $fillIssue->setTimeSpent($fillIssue->getTimeSpent() + $fillTime);
        }


        return $workLogEntries;
    }
}
