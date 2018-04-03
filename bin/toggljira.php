<?php
declare(strict_types=1);

chdir(dirname(__DIR__));

require_once __DIR__ . '/../vendor/autoload.php';

use TogglJira\Bootstrap;

$status = (new Bootstrap(require __DIR__ . '/../config/application.config.php'))
    ->setupConsoleApp()
    ->run();

exit($status);
