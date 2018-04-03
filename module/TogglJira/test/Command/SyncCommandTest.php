<?php
declare(strict_types=1);

namespace TogglJiraTest\Command;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TogglJira\Command\SyncCommand;
use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use Zend\Config\Writer\Json;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Request;

class SyncCommandTest extends TestCase
{
    /**
     * @var SyncCommand
     */
    private $command;

    /**
     * @var MockInterface
     */
    private $syncServiceMock;

    /**
     * @var MockInterface
     */
    private $optionsMock;

    /**
     * @var MockInterface
     */
    private $writerMock;

    /**
     * @var MockInterface
     */
    private $loggerMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->syncServiceMock = \Mockery::mock(SyncService::class);
        $this->optionsMock = \Mockery::mock(SyncOptions::class);
        $this->writerMock = \Mockery::mock(Json::class);
        $this->loggerMock = \Mockery::mock(LoggerInterface::class);

        $this->command = new SyncCommand($this->syncServiceMock, $this->optionsMock, $this->writerMock);
        $this->command->setLogger($this->loggerMock);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testExecute(): void
    {
        $dateTime = '2017-04-15T23:35:00+02:00';

        $requestMock = \Mockery::mock(Request::class);
        $consoleMock = \Mockery::mock(AdapterInterface::class);

        $this->optionsMock->shouldReceive('getLastSync')->andReturn($dateTime);
        $this->optionsMock->shouldReceive('setLastSync');
        $this->optionsMock->shouldReceive('toArray');
        $this->writerMock->shouldReceive('toFile');

        $this->loggerMock->shouldReceive('info')
            ->with("Syncing time entries since {$dateTime}")
            ->once();

        $this->loggerMock->shouldReceive('info')
            ->with('Updated last sync time')
            ->once();

        $this->syncServiceMock->shouldReceive('sync')->with($dateTime);

        $this->assertEquals(1, $this->command->execute($requestMock, $consoleMock));
    }

    /**
     * @return void
     */
    public function testExecuteWithoutStartDate(): void
    {
        $requestMock = \Mockery::mock(Request::class);
        $consoleMock = \Mockery::mock(AdapterInterface::class);

        $this->optionsMock->shouldReceive('getLastSync')->andReturn("");
        $this->optionsMock->shouldReceive('setLastSync');
        $this->optionsMock->shouldReceive('toArray');
        $this->writerMock->shouldReceive('toFile');

        $this->loggerMock->shouldReceive('info')
            ->with(\Mockery::pattern('/(Syncing time entries since)/'))
            ->once();

        $this->loggerMock->shouldReceive('info')
            ->with('Updated last sync time')
            ->once();

        $this->syncServiceMock->shouldReceive('sync');

        $this->assertEquals(1, $this->command->execute($requestMock, $consoleMock));
    }
}