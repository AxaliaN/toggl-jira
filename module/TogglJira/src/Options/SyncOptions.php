<?php
declare(strict_types=1);

namespace TogglJira\Options;

use Zend\Stdlib\AbstractOptions;

class SyncOptions extends AbstractOptions
{
    /**
     * @var \DateTimeInterface
     */
    private $lastSync;

    /**
     * @var string
     */
    private $jiraUsername;

    /**
     * @var string
     */
    private $jiraLoginUsername;

    /**
     * @var string
     */
    private $jiraPassword;

    /**
     * @var string
     */
    private $togglApiKey;

    /**
     * @var string
     */
    private $jiraUrl;

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
     * @param array|null $options
     * @throws \Exception
     */
    public function __construct(array $options = null)
    {
        $this->lastSync = new \DateTimeImmutable('-1 day');

        parent::__construct($options);
    }

    /**
     * @return string
     */
    public function getTogglApiKey(): string
    {
        return $this->togglApiKey;
    }

    /**
     * @param string $togglApiKey
     */
    public function setTogglApiKey(string $togglApiKey): void
    {
        $this->togglApiKey = $togglApiKey;
    }

    /**
     * @return string
     */
    public function getJiraPassword(): string
    {
        return $this->jiraPassword;
    }

    /**
     * @param string $jiraPassword
     */
    public function setJiraPassword(string $jiraPassword): void
    {
        $this->jiraPassword = $jiraPassword;
    }

    /**
     * @return string
     */
    public function getJiraUsername(): string
    {
        return $this->jiraUsername;
    }

    /**
     * @param string $jiraUsername
     */
    public function setJiraUsername(string $jiraUsername): void
    {
        $this->jiraUsername = $jiraUsername;
    }


    /**
     * @return string
     */
    public function getJiraLoginUsername(): string
    {
        return $this->jiraLoginUsername;
    }

    /**
     * @param string $jiraLoginUsername
     */
    public function setJiraLoginUsername(string $jiraLoginUsername): void
    {
        $this->jiraLoginUsername = $jiraLoginUsername;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastSync(): \DateTimeInterface
    {
        return $this->lastSync;
    }

    /**
     * @param \DateTimeInterface $lastSync
     */
    public function setLastSync(\DateTimeInterface $lastSync): void
    {
        $this->lastSync = $lastSync;
    }

    /**
     * @return string
     */
    public function getJiraUrl(): string
    {
        return $this->jiraUrl;
    }

    /**
     * @param string $jiraUrl
     */
    public function setJiraUrl(string $jiraUrl): void
    {
        $this->jiraUrl = $jiraUrl;
    }

    /**
     * @return string
     */
    public function getFillIssueID(): string
    {
        return $this->fillIssueID;
    }

    /**
     * @param string $fillIssueID
     */
    public function setFillIssueID(string $fillIssueID): void
    {
        $this->fillIssueID = $fillIssueID;
    }

    /**
     * @return string
     */
    public function getFillIssueComment(): string
    {
        return $this->fillIssueComment;
    }

    /**
     * @param string $fillIssueComment
     */
    public function setFillIssueComment(string $fillIssueComment): void
    {
        $this->fillIssueComment = $fillIssueComment;
    }

    /**
     * @return bool
     */
    public function isNotifyUsers(): bool
    {
        return $this->notifyUsers;
    }

    /**
     * @param bool $notifyUsers
     */
    public function setNotifyUsers(bool $notifyUsers): void
    {
        $this->notifyUsers = $notifyUsers;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lastSync' => $this->getLastSync(),
            'jiraUrl' => $this->getJiraUrl(),
            'jiraUsername' => $this->getJiraUsername(),
            'jiraLoginUsername' => $this->getJiraLoginUsername(),
            'jiraPassword' => $this->getJiraPassword(),
            'togglApiKey' => $this->getTogglApiKey(),
            'fillIssueID' => $this->getFillIssueID(),
            'fillIssueComment' => $this->getFillIssueComment(),
            'notifyUsers' => $this->isNotifyUsers(),
        ];
    }
}
