<?php
declare(strict_types=1);

namespace TogglJira;

use Zend\Mvc\Application as MvcApplication;
use ZF\Console\Application as ConsoleApplication;
use Zend\Console\Console;
use ZF\Console\Dispatcher;

class Bootstrap
{
    /**
     * @var MvcApplication
     */
    private $mvcApp;

    /**
     * Bootstrap constructor.
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->mvcApp = MvcApplication::init($configuration);
    }

    /**
     * @return ConsoleApplication
     * @throws \Zend\Console\Exception\RuntimeException
     * @throws \Zend\Console\Exception\InvalidArgumentException
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

    /**
     * @return array
     */
    private function readRoutesFromMvc(): array
    {
        return $this->mvcApp->getConfig()['console']['routes'] ?: [];
    }

    /**
     * @return string
     */
    private function locateExceptionHandlerTemplate(): string
    {
        return $this->mvcApp->getConfig()['exception_handler']['template'] ?: '';
    }
}
