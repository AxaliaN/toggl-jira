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
        $startDate = new \DateTime('2017-04-15T23:35:00+02:00');

        $requestMock = \Mockery::mock(Request::class);
        $requestMock->shouldReceive('getParam')->with('startDate', null)->andReturnNull();
        $requestMock->shouldReceive('getParam')->with('endDate', null)->andReturn('tomorrow');
        $requestMock->shouldReceive('getParam')->with('overwrite', false)->andReturnTrue();

        $consoleMock = \Mockery::mock(AdapterInterface::class);

        $this->optionsMock->shouldReceive('getLastSync')->andReturn($startDate);
        $this->optionsMock->shouldReceive('setLastSync');
        $this->optionsMock->shouldReceive('toArray');
        $this->writerMock->shouldReceive('toFile');

        $this->loggerMock->shouldReceive('info')
            ->with('Syncing time entries', ['lastSync' => '2017-04-15T23:35:00+02:00'])
            ->once();

        $this->loggerMock->shouldReceive('info')
            ->with('Updated last sync time')
            ->once();

        $this->syncServiceMock->shouldReceive('sync')->once();

        $this->assertEquals(0, $this->command->execute($requestMock, $consoleMock));
    }
}
