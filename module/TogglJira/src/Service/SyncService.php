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

        /** @var array $timeEntries */
        $timeEntries = $this->togglClient->getTimeEntries(['start_date' => $startDate]);

        foreach ($timeEntries as $timeEntry) {
            $data = [
                'issueID' => explode(' ', $timeEntry['description'])[0],
                'timeSpent' => $timeEntry['duration'],
                'comment' => $timeEntry['description'],
                'spentOn' => $timeEntry['at']
            ];

            if (strpos($data['issueID'], '-') === false) {
                $this->logger->warning('Could not parse issue string, cannot link to Jira');
                continue;
            }

            if ($data['timeSpent'] < 0) {
                $this->logger->info("0 seconds, or timer still running for {$data['issueID']}, skipping");
                continue;
            }

            $workLogEntries[] = $this->workLogHydrator->hydrate($data, new WorkLogEntry());

            $this->logger->info("Found time entry for user story {$data['issueID']}");
        }

        /** @var WorkLogEntry $workLogEntry */
        foreach ($workLogEntries as $workLogEntry) {
            try {
                $this->api->addWorkEntry(
                    $workLogEntry->getIssueID(),
                    $workLogEntry->getTimeSpent(),
                    $this->userID,
                    $workLogEntry->getComment(),
                    $workLogEntry->getSpentOn()->format(DATE_ATOM)
                );

                $this->logger->info("Added worklog entry for issue {$workLogEntry->getIssueID()}");
            } catch (\Exception $e) {
                $this->logger->error("Could not add worklog entry: {$e->getMessage()}");
            }
        }

        $this->logger->info('All done for today, time to go home!');
    }
}
