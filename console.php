#!/usr/bin/env php
<?php

use idoit\Console\IdoitConsoleApplication;
use idoit\Context\Context;

// Set error reporting.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
$g_absdir = __DIR__;

if (file_exists(__DIR__ . '/src/config.inc.php')) {
    require __DIR__ . "/src/config.inc.php";
}
require __DIR__ . '/src/bootstrap.inc.php';

try {
    chdir($g_absdir);
    if (!file_exists(__DIR__ . '/src/config.inc.php') && file_exists(__DIR__ . '/src/classes/modules/console/console.php')) {
        include __DIR__ . '/src/classes/modules/console/console.php';
        exit;
    }
    $application = new IdoitConsoleApplication();
    $application->useEventDispatcher();

    Context::instance()->setOrigin(Context::ORIGIN_CONSOLE);
    $application->run();
} catch (Exception $e) {
    die($e->getMessage());
}
