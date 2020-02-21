<?php
declare(strict_types=1);

namespace TogglJiraTest\Handler;

use Exception;
use Mockery;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use TogglJira\Command\CommandInterface;
use TogglJira\Exception\CommandNotFoundException;
use TogglJira\Handler\CommandHandler;
use TogglJiraTest\BaseContainerTest;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Request;
use Zend\Stdlib\Parameters;
use ZF\Console\Route;

class CommandHandlerTest extends BaseContainerTest
{
    /**
     * @throws CommandNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvoke(): void
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getName')->once()->andReturn('gimmeSomething');
        $route->shouldReceive('getMatches')->once()->andReturn(['test' => true]);

        $console = Mockery::mock(AdapterInterface::class);

        $request = Mockery::mock(Request::class);
        $params = Mockery::mock(Parameters::class);
        $params->shouldReceive('fromArray')->once()->with(['test' => true]);
        $request->shouldReceive('getParams')->once()->andReturn($params);

        $executeCommand = Mockery::mock(CommandInterface::class);
        $executeCommand->shouldReceive('execute')->once()->withArgs([$request, $console])->andReturn(0);

        $container = $this->getContainer();
        $container->shouldReceive('has')->once()->with('gimmeSomething')->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with('gimmeSomething')
            ->andReturn($executeCommand);

        $container->shouldReceive('get')->once()->with(Request::class)->andReturn($request);

        $command = new CommandHandler($container);
        $this->assertSame(0, $command->__invoke($route, $console));
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeException(): void
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getName')->once()->andReturn('gimmeSomething');
        $route->shouldReceive('getMatches')->once()->andReturn(['test' => true]);

        $console = Mockery::mock(AdapterInterface::class);

        $container = $this->getContainer();
        $container->shouldReceive('has')->once()->with('gimmeSomething')->andReturn(false);

        $this->expectException(CommandNotFoundException::class);

        $command = new CommandHandler($container);
        $this->assertSame(1, $command->__invoke($route, $console));
    }

    /**
     * @throws CommandNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeExceptionInCommand(): void
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getName')->once()->andReturn('mockCommand');
        $route->shouldReceive('getMatches')->once()->andReturn(['test' => true]);

        $request = Mockery::mock(Request::class);
        $params = Mockery::mock(Parameters::class);
        $params->shouldReceive('fromArray')->once()->with(['test' => true]);
        $request->shouldReceive('getParams')->once()->andReturn($params);

        $console = Mockery::mock(AdapterInterface::class);

        $commandMock = Mockery::mock(CommandInterface::class);
        $commandMock->shouldReceive('execute')->andThrow(Exception::class);

        $container = $this->getContainer();
        $container->shouldReceive('has')->once()->with('mockCommand')->andReturn(true);
        $container->shouldReceive('get')->once()->with('mockCommand')->andReturn($commandMock);
        $container->shouldReceive('get')->once()->with(Request::class)->andReturn($request);

        $loggerMock = Mockery::mock(LoggerInterface::class);
        $loggerMock->shouldReceive('error')->once();

        $command = new CommandHandler($container);

        $command->setLogger($loggerMock);
        $this->assertSame(1, $command->__invoke($route, $console));
    }
}
