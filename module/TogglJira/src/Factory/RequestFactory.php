<?php
declare(strict_types=1);

namespace TogglJira\Factory;

use Interop\Container\ContainerInterface;
use Zend\Console\Request;
use Zend\ServiceManager\Factory\FactoryInterface;

class RequestFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Request();
    }
}
