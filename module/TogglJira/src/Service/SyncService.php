<?php
declare(strict_types=1);

namespace TogglJira\Service;

use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
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
     * @var GuzzleClient
     */
    private $togglClient;
    /**
     * @var WorkLogHydrator
     */
    private $workLogHydrator;
    /**
     * @var string
     */
    private $userID;

    /**
     * @param Api $api
     * @param GuzzleClient $togglClient
     * @param WorkLogHydrator $workLogHydrator
     * @param string $userID
     */
    public function __construct(Api $api, GuzzleClient $togglClient, WorkLogHydrator $workLogHydrator, string $userID)
    {
        $this->api = $api;
        $this->togglClient = $togglClient;
        $this->workLogHydrator = $workLogHydrator;
        $this->userID = $userID;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function sync(string $startDate): void
    {
        $workLogEntries = [];

        try {
            /** @var array $timeEntries */
            $timeEntries = $this->togglClient->getTimeEntries(['start_date' => $startDate]);
        } catch (\Exception $e) {
            $this->logger->error(
                "Failed to get time entries from Toggl: {$e->getMessage()}",
                ['exception' => $e]
            );
            return;
        }

        foreach ($timeEntries as $timeEntry) {
            $workLogEntry = $this->parseTimeEntry($timeEntry);

            if (!$workLogEntry) {
                continue;
            }

            $workLogEntries[] = $workLogEntry;

            $this->logger->info("Found time entry for user story {$workLogEntry->getIssueID()}");
        }

        /** @var WorkLogEntry $workLogEntry */
        foreach ($workLogEntries as $workLogEntry) {
            try {
                $this->api->addWorkLogEntry(
                    $workLogEntry->getIssueID(),
                    $workLogEntry->getTimeSpent(),
                    $this->userID,
                    $workLogEntry->getComment(),
                    $workLogEntry->getSpentOn()->format(DATE_ATOM)
                );

                $this->logger->info("Added worklog entry for issue {$workLogEntry->getIssueID()}");
            } catch (\Exception $e) {
                $this->logger->error("Could not add worklog entry: {$e->getMessage()}", ['exception' => $e]);
            }
        }

        $this->logger->info('All done for today, time to go home!');
    }

    /**
     * @param array $timeEntry
     * @return WorkLogEntry|null
     * @throws \Exception
     */
    private function parseTimeEntry(array $timeEntry): ?WorkLogEntry
    {
        $data = [
            'issueID' => explode(' ', $timeEntry['description'])[0],
            'timeSpent' => $timeEntry['duration'],
            'comment' => $timeEntry['description'],
            'spentOn' => $timeEntry['at']
        ];

        if (strpos($data['issueID'], '-') === false) {
            $this->logger->warning('Could not parse issue string, cannot link to Jira');
            return null;
        }

        if ($data['timeSpent'] < 0) {
            $this->logger->info("0 seconds, or timer still running for {$data['issueID']}, skipping");
            return null;
        }

        return $this->workLogHydrator->hydrate($data, new WorkLogEntry());
    }
}
