<?php
declare(strict_types=1);

namespace TogglJiraTest\Command;

use Psr\Log\LoggerInterface;
use TogglJira\Command\SyncCommand;
use TogglJira\Command\SyncCommandFactory;
use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use TogglJiraTest\BaseContainerTest;

class SyncCommandFactoryTest extends BaseContainerTest
{
    public function testInvoke(): void
    {
        $syncOptionMock = \Mockery::mock(SyncOptions::class);
        $syncServiceMock = \Mockery::mock(SyncService::class);
        $loggerMock = \Mockery::mock(LoggerInterface::class);

        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->withArgs([SyncOptions::class])
            ->andReturn($syncOptionMock);

        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->withArgs([SyncService::class])
            ->andReturn($syncServiceMock);

        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->withArgs(['Logger'])
            ->andReturn($loggerMock);

        $factory = new SyncCommandFactory();
        $instance = $factory->__invoke($this->getContainer(), SyncCommand::class);

        $this->assertInstanceOf(SyncCommand::class, $instance);
    }
}
