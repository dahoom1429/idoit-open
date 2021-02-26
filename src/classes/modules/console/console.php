#!/usr/bin/env php
<?php

use idoit\Context\Context;
use idoit\Module\Console\Console\InstallConsoleApplication;

// Set error reporting.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
$g_absdir = __DIR__ . '/../../../..';
if (file_exists($g_absdir . '/src/config.inc.php')) {
    die('i-doit is already installed. Please use the usual console.php');
}
require $g_absdir . '/src/bootstrap.inc.php';
require $g_absdir . '/src/classes/modules/console/init.php';

try {
    chdir($g_absdir);
    $application = new InstallConsoleApplication();

    $application->useEventDispatcher();

    Context::instance()->setOrigin(Context::ORIGIN_CONSOLE);
    $application->run();
} catch (Exception $e) {
    die($e->getMessage());
}
