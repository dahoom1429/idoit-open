<?php

namespace idoit\Module\Console\Console\Command\idoit\Update;

use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Update\ProcessUpdate;
use isys_application;
use isys_update_log;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Second stage of the update - xml/sql/file updates, migrations, renew properties
 */
class UpdateSecondStepCommand extends UpdateBase
{
    /**
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('update-2')
            ->setHidden(true);
    }

    /**
     * @return Step
     */
    protected function createStep()
    {
        global $g_absdir, $g_temp_dir, $g_log_dir;

        include_once $g_absdir . '/updates/constants.inc.php';

        $g_temp_dir = $g_absdir . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $g_log_dir = $g_absdir . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;

        $debugFile = date("Y-m-d") . '-' . $this->getValue('version') . '_idoit_update.log';
        $debugLog = $g_log_dir . $debugFile;

        $log = isys_update_log::get_instance();
        register_shutdown_function(function () use ($log, $debugLog) {
            $log->write_debug(basename($debugLog));
        });

        $db = isys_application::instance()->container->get('database_system');

        $updatePath = $this->getValue('update.path') . 'v' . $this->getValue('version');

        return new CollectionStep('i-doit update', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new ProcessUpdate($updatePath, $db),
        ]);
    }
}
