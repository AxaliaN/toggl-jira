<?php
declare(strict_types=1);

namespace TogglJira\Entity;

class WorkLogEntry
{
    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $issueID;

    /**
     * @var \DateTimeInterface
     */
    private $spentOn;

    /**
     * @var int
     */
    private $timeSpent;

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
     * @return \DateTimeInterface
     */
    public function getSpentOn(): \DateTimeInterface
    {
        return $this->spentOn;
    }

    /**
     * @param \DateTimeInterface $spentOn
     */
    public function setSpentOn(\DateTimeInterface $spentOn): void
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
