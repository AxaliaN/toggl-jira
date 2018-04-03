<?php
declare(strict_types=1);

namespace TogglJiraTest\Factory;

use TogglJira\Factory\RequestFactory;
use TogglJiraTest\BaseContainerTestCase;
use Zend\Console\Request;

class RequestFactoryTest extends BaseContainerTestCase
{
    public function testInvoke()
    {
        $factory = new RequestFactory();

        $this->assertInstanceOf(Request::class, $factory($this->getContainer(), ''));
    }
}
