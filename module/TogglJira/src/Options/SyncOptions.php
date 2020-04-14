<?php
declare(strict_types=1);

namespace TogglJira\Options;

use Zend\Stdlib\AbstractOptions;

class SyncOptions extends AbstractOptions
{
    /**
     * @var string
     */
    private $jiraAccountId;
    /**
     * @var string
     */
    private $jiraApiKey;
    /**
     * @var string
     */
    private $jiraEmail;
    /**
     * @var string
     */
    private $jiraUrl;
    /**
     * @var \DateTimeInterface
     */
    private $lastSync;

    /**
     * @var string
     */
    private $togglApiKey;

    public function __construct($options = null)
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
    public function getjiraApiKey(): string
    {
        return $this->jiraApiKey;
    }

    /**
     * @param string $jiraApiKey
     */
    public function setjiraApiKey(string $jiraApiKey): void
    {
        $this->jiraApiKey = $jiraApiKey;
    }

    public function getjiraAccountId(): string
    {
        return $this->jiraAccountId;
    }

    /**
     * @param string $jiraAccountId
     */
    public function setjiraAccountId(string $jiraAccountId): void
    {
        $this->jiraAccountId = $jiraAccountId;
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


    public function getjiraEmail(): string
    {
        return $this->jiraEmail;
    }

    /**
     * @param string $jiraEmail
     */
    public function setJiraEmail(string $jiraEmail): void
    {
        $this->jiraEmail = $jiraEmail;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lastSync' => $this->getLastSync(),
            'jiraUrl' => $this->getJiraUrl(),
            'jiraEmail' => $this->getjiraEmail(),
            'jiraAccountId' => $this->getjiraAccountId(),
            'jiraApiKey' => $this->getjiraApiKey(),
            'togglApiKey' => $this->getTogglApiKey(),
        ];
    }
}
