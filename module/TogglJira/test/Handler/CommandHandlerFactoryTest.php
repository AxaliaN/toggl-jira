<?php
declare(strict_types=1);

namespace ACSI\Worker\Test\Handler;

use Psr\Log\LoggerInterface;
use TogglJira\Handler\CommandHandler;
use TogglJira\Handler\CommandHandlerFactory;
use TogglJiraTest\BaseContainerTestCase;

class CommandHandlerFactoryTest extends BaseContainerTestCase
{
    public function testInvoke()
    {
        $factory = new CommandHandlerFactory();

        $loggerMock = \Mockery::mock(LoggerInterface::class);
        $this->getContainer()
            ->shouldReceive('get')
            ->once()
            ->withArgs(['AcsiEventHandling\Logger'])
            ->andReturn($loggerMock);

        $this->assertInstanceOf(CommandHandler::class, $factory($this->getContainer(), ''));
    }
}
