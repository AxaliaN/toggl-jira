<?php
declare(strict_types=1);

namespace TogglJiraTest\Service;

use Exception;
use Interop\Container\Exception\ContainerException;
use Mockery;
use Psr\Log\LoggerInterface;
use TogglJira\Options\SyncOptions;
use TogglJira\Service\SyncService;
use TogglJira\Service\SyncServiceFactory;
use TogglJiraTest\BaseContainerTest;

class SyncServiceFactoryTest extends BaseContainerTest
{
    /**
     * @throws Exception
     * @throws ContainerException
     */
    public function testInvoke(): void
    {
        $syncOptionMock = Mockery::mock(SyncOptions::class);
        $syncOptionMock->shouldReceive('getJiraUrl')->andReturn('http://www.example.com');
        $syncOptionMock->shouldReceive('getJiraUsername')->andReturn('foo');
        $syncOptionMock->shouldReceive('getJiraLoginUsername')->andReturn('foo@example.com');
        $syncOptionMock->shouldReceive('getJiraPassword')->andReturn('bar');
        $syncOptionMock->shouldReceive('getTogglApiKey')->andReturn('baz');
        $syncOptionMock->shouldReceive('getFillIssueID')->andReturn('FOO-01');
        $syncOptionMock->shouldReceive('isNotifyUsers')->andReturn(true);

        $loggerMock = Mockery::mock(LoggerInterface::class);

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
