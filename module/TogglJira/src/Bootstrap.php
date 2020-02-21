<?php
declare(strict_types=1);

namespace TogglJira;

use Zend\Console\Console;
use Zend\Console\Exception\InvalidArgumentException;
use Zend\Console\Exception\RuntimeException;
use Zend\Mvc\Application as MvcApplication;
use ZF\Console\Application as ConsoleApplication;
use ZF\Console\Dispatcher;

class Bootstrap
{
    /**
     * @var MvcApplication
     */
    private $mvcApp;

    public function __construct(array $configuration)
    {
        $this->mvcApp = MvcApplication::init($configuration);
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setupConsoleApp(): ConsoleApplication
    {
        $services = $this->mvcApp->getServiceManager();

        $version = $services->get('Config')['version'];

        $application = new ConsoleApplication(
            'TogglJira',
            $version,
            $this->readRoutesFromMvc(),
            Console::getInstance(),
            new Dispatcher($services)
        );

        $application->getExceptionHandler()->setMessageTemplate(
            file_get_contents($this->locateExceptionHandlerTemplate())
        );

        return $application;
    }

    private function readRoutesFromMvc(): array
    {
        return $this->mvcApp->getConfig()['console']['routes'] ?: [];
    }

    private function locateExceptionHandlerTemplate(): string
    {
        return $this->mvcApp->getConfig()['exception_handler']['template'] ?: '';
    }
}
