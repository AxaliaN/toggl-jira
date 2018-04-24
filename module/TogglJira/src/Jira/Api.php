<?php
declare(strict_types=1);

namespace TogglJira\Jira;

use chobie\Jira\Api as BaseApi;

class Api extends BaseApi
{
    /**
     * @param string $issueID
     * @param int $seconds
     * @param string $accountId
     * @param string $comment
     * @param string $created
     * @return array|BaseApi\Result|false
     */
    public function addWorkLogEntry(
        string $issueID,
        int $seconds,
        string $accountId,
        string $comment,
        string $created
    ) {
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

        if (isset($workLogResult['worklogs'])) {
            foreach ($workLogResult['worklogs'] as $workLog) {
                if ($workLog['author']['accountId'] === $accountId) {
                    $params = ['timeSpentSeconds' => $seconds];
                    return $this->api(self::REQUEST_PUT, "/rest/api/2/issue/{$issueID}/worklog/{$workLog['id']}?adjustEstimate=auto", $params);
                }
            }
        }

        $result = $this->api(self::REQUEST_POST, "/rest/api/2/issue/{$issueID}/worklog?adjustEstimate=auto", $params);

        return $result;
    }
}
