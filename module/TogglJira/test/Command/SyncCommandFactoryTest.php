<?php
declare(strict_types=1);

namespace TogglJiraTest\Command;

use Psr\Log\LoggerInterface;
use TogglJira\Command\SyncCommand;
use TogglJira\Command\SyncCommandFactory;
use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use TogglJiraTest\BaseContainerTestCase;

class SyncCommandFactoryTest extends BaseContainerTestCase
{
    public function testInvoke()
    {
        $syncOptionMock = \Mockery::mock(SyncOptions::class);
        $syncServiceMock = \Mockery::mock(SyncService::class);
        $loggerMock = \Mockery::mock(LoggerInterface::class);

        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->with(SyncOptions::class)
            ->andReturn($syncOptionMock);

        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->with(SyncService::class)
            ->andReturn($syncServiceMock);

        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->with('AcsiEventHandling\Logger')
            ->andReturn($loggerMock);

        $factory = new SyncCommandFactory();
        $instance = $factory->__invoke($this->getContainer(), SyncCommand::class);

        $this->assertInstanceOf(SyncCommand::class, $instance);
    }
}
