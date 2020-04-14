<?php
declare(strict_types=1);

namespace TogglJiraTest\Service;

use Psr\Log\LoggerInterface;
use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use TogglJira\Service\SyncServiceFactory;
use TogglJiraTest\BaseContainerTest;

class SyncServiceFactoryTest extends BaseContainerTest
{
    /**
     * @return void
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function testInvoke(): void
    {
        $syncOptionMock = \Mockery::mock(SyncOptions::class);
        $syncOptionMock->shouldReceive('getJiraUrl')->andReturn('http://www.example.com');
        $syncOptionMock->shouldReceive('getjiraEmail')->andReturn('foo@baz.com');
        $syncOptionMock->shouldReceive('getjiraAccountId')->andReturn('foo');
        $syncOptionMock->shouldReceive('getjiraApiKey')->andReturn('bar');
        $syncOptionMock->shouldReceive('getTogglApiKey')->andReturn('baz');

        $loggerMock = \Mockery::mock(LoggerInterface::class);

        $this->getContainer()
            ->shouldReceive('get')
            ->withArgs([SyncOptions::class])
            ->andReturn($syncOptionMock);

        $this->getContainer()
            ->shouldReceive('get')
            ->withArgs(['Logger'])
            ->andReturn($loggerMock);

        $factory = new SyncServiceFactory();
        $instance = $factory->__invoke($this->getContainer(), SyncService::class);

        $this->assertInstanceOf(SyncService::class, $instance);
    }
}
