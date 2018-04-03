<?php
declare(strict_types=1);

namespace TogglJira\Command;

use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use Zend\Config\Writer\Json;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Request;

class SyncCommand implements CommandInterface
{
    /**
     * @var SyncService
     */
    private $service;

    /**
     * @var SyncOptions
     */
    private $syncOptions;

    /**
     * @var Json
     */
    private $writer;

    /**
     * @param SyncService $service
     * @param SyncOptions $syncOptions
     * @param Json $writer
     */
    public function __construct(SyncService $service, SyncOptions $syncOptions, Json $writer)
    {
        $this->service = $service;
        $this->syncOptions = $syncOptions;
        $this->writer = $writer;
    }

    /**
     * @param Request $request
     * @param AdapterInterface $console
     * @return int
     * @throws \Exception
     */
    public function execute(Request $request, AdapterInterface $console): int
    {
        if ($this->syncOptions->getLastSync() === "") {
            $this->syncOptions->setLastSync(
                (new \DateTimeImmutable('-1 day'))->format(DATE_ATOM)
            );
        }

        $console->writeLine("Syncing time entries since {$this->syncOptions->getLastSync()}");
        $this->service->sync($this->syncOptions->getLastSync());

        $this->syncOptions->setLastSync((new \DateTimeImmutable())->format(DATE_ATOM));

        $this->writer->toFile('config.json', $this->syncOptions->toArray());

        $console->writeLine('Updated last sync time');

        return 1;
    }
}
