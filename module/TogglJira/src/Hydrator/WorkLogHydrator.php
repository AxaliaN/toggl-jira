<?php
declare(strict_types=1);

namespace TogglJira\Hydrator;

use TogglJira\Entity\WorkLogEntry;
use Zend\Hydrator\HydratorInterface;

class WorkLogHydrator implements HydratorInterface
{

    /**
     * Extract values from an object
     *
     * @param  WorkLogEntry $object
     * @return array
     */
    public function extract($object)
    {
        return [
            'issueID' => $object->getIssueID(),
            'timeSpent' => $object->getTimeSpent(),
            'comment' => $object->getComment(),
            'spentOn' => $object->getSpentOn()
        ];
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  WorkLogEntry $object
     * @return object
     * @throws \Exception
     */
    public function hydrate(array $data, $object): WorkLogEntry
    {
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
