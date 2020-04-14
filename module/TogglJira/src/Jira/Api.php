<?php
declare(strict_types=1);

namespace TogglJira\Jira;

use chobie\Jira\Api as BaseApi;

class Api extends BaseApi
{
    /**
     * @param string $accountId
     * @return array
     */
    public function getUser(string $accountId): array
    {
        $userDetails = $this->api(self::REQUEST_GET, "/rest/api/2/user", [
            'accountId' => $accountId
        ]);

        return $userDetails->getResult();
    }

    /**
     * @param string $issueID
     * @param int $seconds
     * @param string $accountId
     * @param string $comment
     * @param string $created
     * @param bool $overwrite
     * @param bool $notifyUsers
     * @return array|BaseApi\Result|false
     * @throws \Exception
     */
    public function addWorkLogEntry(
        string $issueID,
        int $seconds,
        string $accountId,
        string $comment,
        string $created,
        bool $overwrite,
        bool $notifyUsers = true
    ) {
        $notify = $notifyUsers ? 'true' : 'false';

        $params = [
            'timeSpentSeconds' => $seconds,
            'author' => [
                'accountId' => $accountId,
            ],
            'comment' => $comment,
            'started' => $created,
        ];

        $worklogResponse = $this->api(self::REQUEST_GET, "/rest/api/2/issue/{$issueID}/worklog");
        $workLogResult = $worklogResponse->getResult();

        $startedDay = (new \DateTimeImmutable($params['started']))->format('d-m-Y');

        if (isset($workLogResult['worklogs'])) {
            foreach ($workLogResult['worklogs'] as $workLog) {
                $workLogStartedDay = (new \DateTimeImmutable($workLog['started']))->format('d-m-Y');

                if ($startedDay === $workLogStartedDay &&
                    $workLog['author']['accountId'] === $accountId
                ) {
                    if (!$overwrite) {
                        return $this->api(
                            self::REQUEST_PUT,
                            "/rest/api/2/issue/{$issueID}/worklog/{$workLog['id']}?adjustEstimate=auto&notifyUsers={$notify}",
                            $params
                        );
                    }

                    /**
                     * When overwriting the worklogs, delete the existing worklogs first before recreating.
                     */
                    $this->api(self::REQUEST_DELETE, "/rest/api/2/issue/{$issueID}/worklog/{$workLog['id']}");
                }
            }
        }

        $result = $this->api(self::REQUEST_POST, "/rest/api/2/issue/{$issueID}/worklog?adjustEstimate=auto&notifyUsers={$notify}", $params);

        return $result;
    }
}
