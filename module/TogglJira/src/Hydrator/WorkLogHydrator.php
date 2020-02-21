<?php
declare(strict_types=1);

namespace TogglJira\Hydrator;

use Exception;
use TogglJira\Entity\WorkLogEntry;
use Zend\Hydrator\HydratorInterface;

class WorkLogHydrator implements HydratorInterface
{
    public function extract($object): array
    {
        return [
            'issueID' => $object->getIssueID(),
            'timeSpent' => $object->getTimeSpent(),
            'comment' => $object->getComment(),
            'spentOn' => $object->getSpentOn()
        ];
    }

    /**
     * @throws Exception
     */
    public function hydrate(array $data, $object): WorkLogEntry
    {
        /** @var WorkLogEntry $object */
        if (isset($data['issueID'])) {
            $object->setIssueID($data['issueID']);
        }

        if (isset($data['timeSpent'])) {
            $object->setTimeSpent($data['timeSpent']);
        }

        if (isset($data['comment'])) {
            $object->setComment($data['comment']);
        }

        if (isset($data['spentOn'])) {
            $object->setSpentOn(new \DateTimeImmutable($data['spentOn']));
        }

        return $object;
    }
}
