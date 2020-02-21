<?php
declare(strict_types=1);

namespace TogglJiraTest\Handler;

use Psr\Log\LoggerInterface;
use TogglJira\Handler\CommandHandler;
use TogglJira\Handler\CommandHandlerFactory;
use TogglJiraTest\BaseContainerTest;

class CommandHandlerFactoryTest extends BaseContainerTest
{
    public function testInvoke(): void
    {
        $factory = new CommandHandlerFactory();

        $loggerMock = \Mockery::mock(LoggerInterface::class);
        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->withArgs(['Logger'])
            ->andReturn($loggerMock);

        $this->assertInstanceOf(CommandHandler::class, $factory($this->getContainer(), ''));
    }
}
