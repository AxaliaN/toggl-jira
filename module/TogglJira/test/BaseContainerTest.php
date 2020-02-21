<?php
declare(strict_types=1);

namespace TogglJiraTest;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class BaseContainerTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $container;

    public function __destruct()
    {
        Mockery::close();
    }

    protected function setUp(): void
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        parent::setUp();
    }

    protected function getContainer(): MockInterface
    {
        if ($this->container === null) {
            $this->container = \Mockery::mock(ServiceManager::class);
        }

        return $this->container;
    }
}
