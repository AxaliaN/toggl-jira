<?php
declare(strict_types=1);

namespace TogglJira\Options;

use DateTimeInterface;
use Exception;
use Zend\Stdlib\AbstractOptions;

class SyncOptions extends AbstractOptions
{
    /**
     * @var DateTimeInterface
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
     * @throws Exception
     */
    public function __construct(array $options = null)
    {
        $this->lastSync = new \DateTimeImmutable('-1 day');

        parent::__construct($options);
    }

    public function getTogglApiKey(): string
    {
        return $this->togglApiKey;
    }

    public function setTogglApiKey(string $togglApiKey): void
    {
        $this->togglApiKey = $togglApiKey;
    }

    public function getJiraPassword(): string
    {
        return $this->jiraPassword;
    }

    public function setJiraPassword(string $jiraPassword): void
    {
        $this->jiraPassword = $jiraPassword;
    }

    public function getJiraUsername(): string
    {
        return $this->jiraUsername;
    }

    public function setJiraUsername(string $jiraUsername): void
    {
        $this->jiraUsername = $jiraUsername;
    }

    public function getJiraLoginUsername(): string
    {
        return $this->jiraLoginUsername;
    }

    public function setJiraLoginUsername(string $jiraLoginUsername): void
    {
        $this->jiraLoginUsername = $jiraLoginUsername;
    }

    public function getLastSync(): DateTimeInterface
    {
        return $this->lastSync;
    }

    public function setLastSync(DateTimeInterface $lastSync): void
    {
        $this->lastSync = $lastSync;
    }

    public function getJiraUrl(): string
    {
        return $this->jiraUrl;
    }

    public function setJiraUrl(string $jiraUrl): void
    {
        $this->jiraUrl = $jiraUrl;
    }

    public function getFillIssueID(): string
    {
        return $this->fillIssueID;
    }

    public function setFillIssueID(string $fillIssueID): void
    {
        $this->fillIssueID = $fillIssueID;
    }

    public function getFillIssueComment(): string
    {
        return $this->fillIssueComment;
    }

    public function setFillIssueComment(string $fillIssueComment): void
    {
        $this->fillIssueComment = $fillIssueComment;
    }

    public function isNotifyUsers(): bool
    {
        return $this->notifyUsers;
    }

    public function setNotifyUsers(bool $notifyUsers): void
    {
        $this->notifyUsers = $notifyUsers;
    }

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
