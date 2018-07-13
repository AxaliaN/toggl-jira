<?php
declare(strict_types=1);

namespace TogglJira\Service;

use AJT\Toggl\TogglClient;
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
     * @param Api $api
     * @param GuzzleClient $togglClient
     * @param WorkLogHydrator $workLogHydrator
     * @param string $username
     */
    public function __construct(Api $api, GuzzleClient $togglClient, WorkLogHydrator $workLogHydrator, string $username)
    {
        $this->api = $api;
        $this->togglClient = $togglClient;
        $this->workLogHydrator = $workLogHydrator;
        $this->username = $username;
    }

    /**
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param bool $overwrite
     * @return void
     * @throws Exception
     */
    public function sync(\DateTimeInterface $startDate, \DateTimeInterface $endDate, bool $overwrite): void
    {
        $user = $this->api->getUser($this->username);

        if (!isset($user['accountId'])) {
            throw new RuntimeException("User with username {$this->username} not found");
        }

        $timeEntries = $this->getTimeEntries($startDate, $endDate);

        if ($timeEntries) {
            $this->addWorkLogsToApi($this->parseTimeEntries($timeEntries), $user, $overwrite);
        }

        $this->logger->info('All done for today, time to go home!');
    }

    /**
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array|null
     */
    private function getTimeEntries(\DateTimeInterface $startDate, \DateTimeInterface $endDate): ?array
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
        $existingWorkLog->setComment($existingWorkLog->getComment() . "\n" . $newWorkLog->getComment());

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
     * @param bool $overwrite
     * @return void
     */
    private function addWorkLogsToApi(array $workLogEntries, array $user, bool $overwrite): void
    {
        /** @var WorkLogEntry $workLogEntry */
        foreach ($workLogEntries as $workLogEntry) {
            try {
                $this->api->addWorkLogEntry(
                    $workLogEntry->getIssueID(),
                    $workLogEntry->getTimeSpent(),
                    $user['accountId'],
                    $workLogEntry->getComment(),
                    $workLogEntry->getSpentOn()->format('Y-m-d\TH:i:s.vO'),
                    $overwrite
                );

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
}
