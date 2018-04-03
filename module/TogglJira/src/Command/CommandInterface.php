<?php
declare(strict_types=1);

namespace TogglJira\Command;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Console;
use Zend\Console\Request;

interface CommandInterface
{
    /**
     * @param Request $request
     * @param AdapterInterface|Console $console
     * @return int
     */
    public function execute(Request $request, AdapterInterface $console): int;
}
