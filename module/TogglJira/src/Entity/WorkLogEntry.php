<?php
declare(strict_types=1);

namespace TogglJira\Entity;

use DateTimeImmutable;

class WorkLogEntry
{
    /**
     * @var string
     */
    private $issueID;

    /**
     * @var int
     */
    private $timeSpent;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var DateTimeImmutable
     */
    private $spentOn;

    /**
     * @return string
     */
    public function getIssueID(): string
    {
        return $this->issueID;
    }

    /**
     * @param string $issueID
     */
    public function setIssueID(string $issueID): void
    {
        $this->issueID = $issueID;
    }

    /**
     * @return int
     */
    public function getTimeSpent(): int
    {
        return $this->timeSpent;
    }

    /**
     * @param int $timeSpent
     */
    public function setTimeSpent(int $timeSpent): void
    {
        $this->timeSpent = $timeSpent;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getSpentOn(): DateTimeImmutable
    {
        return $this->spentOn;
    }

    /**
     * @param DateTimeImmutable $spentOn
     */
    public function setSpentOn(DateTimeImmutable $spentOn): void
    {
        $this->spentOn = $spentOn;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
}
