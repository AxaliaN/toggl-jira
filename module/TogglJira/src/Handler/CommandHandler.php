<?php
declare(strict_types=1);

namespace TogglJira\Handler;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TogglJira\Exception\CommandNotFoundException;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Exception\RuntimeException;
use Zend\Console\Request;
use ZF\Console\Route;

class CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws RuntimeException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws CommandNotFoundException
     */
    public function __invoke(Route $route, AdapterInterface $console): int
    {
        $name = $route->getName();
        $matches = $route->getMatches();

        if (!$this->container->has($name)) {
            throw new CommandNotFoundException("The command {$name} was not found in the service manager.", 1);
        }

        $command = $this->container->get($name);
        /** @var Request $request */
        $request = $this->container->get(Request::class);
        $request->getParams()->fromArray($matches);

        try {
            return $command->execute($request, $console);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['context' => $e]);
        }

        return 1;
    }
}
