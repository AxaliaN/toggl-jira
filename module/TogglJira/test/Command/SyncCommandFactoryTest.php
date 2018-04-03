<?php
declare(strict_types=1);

namespace TogglJiraTest\Command;

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

        $this->getContainer()
            ->shouldReceive('get')
            ->with(SyncOptions::class)
            ->andReturn($syncOptionMock);

        $this->getContainer()
            ->shouldReceive('get')
            ->with(SyncService::class)
            ->andReturn($syncServiceMock);

        $factory = new SyncCommandFactory();
        $instance = $factory->__invoke($this->getContainer(), SyncCommand::class);

        $this->assertInstanceOf(SyncCommand::class, $instance);
    }
}
