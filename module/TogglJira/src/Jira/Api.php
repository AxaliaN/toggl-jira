<?php
declare(strict_types=1);

namespace TogglJira\Jira;

use chobie\Jira\Api as BaseApi;

class Api extends BaseApi
{
    /**
     * @param string $issueID
     * @param int $seconds
     * @param string $userID
     * @param string $comment
     * @param string $created
     * @return array|BaseApi\Result|false
     */
    public function addWorkEntry(
        string $issueID,
        int $seconds,
        string $userID,
        string $comment,
        string $created
    ) {
        $params = [
            'author' => [
                'accountId' => $userID,
            ],
            'created' => $created,
            'timeSpentSeconds' => $seconds,
            'comment' => $comment
        ];

        return $this->api(self::REQUEST_POST, "/rest/api/2/issue/{$issueID}/worklog?adjustEstimate=auto", $params);
    }
}
