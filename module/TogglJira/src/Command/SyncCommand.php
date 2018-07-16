<?php
declare(strict_types=1);

namespace TogglJira\Command;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use Zend\Config\Writer\Json;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Request;

class SyncCommand implements CommandInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        $reqStartDate = $request->getParam('startDate', null);
        $reqEndDate = $request->getParam('endDate', null);
        $startDate = $reqStartDate ? new \DateTime($reqStartDate) : $this->syncOptions->getLastSync();
        $endDate = $reqEndDate ? new \DateTime($reqEndDate) : new \DateTime('now');
        $overwrite = (bool) $request->getParam('overwrite', false);

        $this->logger->info(
            'Syncing time entries',
            ['lastSync' => $startDate->format(DATE_ATOM)]
        );

        $this->service->sync($startDate, $endDate, $overwrite);
        $this->syncOptions->setLastSync($endDate);

        $this->writer->toFile('config.json', $this->syncOptions->toArray());

        $this->logger->info('Updated last sync time');

        return 0;
    }
}
