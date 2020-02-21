<?php
declare(strict_types=1);

namespace TogglJiraTest\Factory;

use TogglJira\Factory\RequestFactory;
use TogglJiraTest\BaseContainerTest;
use Zend\Console\Request;

class RequestFactoryTest extends BaseContainerTest
{
    public function testInvoke(): void
    {
        $factory = new RequestFactory();

        $this->assertInstanceOf(Request::class, $factory($this->getContainer(), ''));
    }
}
