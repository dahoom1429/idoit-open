<?php

namespace idoit\Module\Console\Steps\Addon;

use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\IfCheck;
use idoit\Module\Console\Steps\IncludeStep;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use isys_component_database;

class InstallAddon implements Step, Undoable
{
    /**
     * @var CollectionStep
     */
    private $step;

    /**
     * InstallAddon constructor.
     *
     * @param string                  $addon
     * @param array                   $tenantIds
     * @param isys_component_database $db
     */
    public function __construct(string $addon, array $tenantIds, isys_component_database $db)
    {
        global $g_absdir;

        $installScript = $g_absdir . '/src/classes/modules/' . $addon . '/install/isys_module_' . $addon . '_install.class.php';

        $this->step = new CollectionStep('Install add-on ' . $addon, [
            new CollectionStep('Check requirements of Add-on ' . $addon, []),
            new IfCheck('Check install scripts', new FileExistsCheck($installScript), new IncludeStep('Include install script', $installScript)),
            new CollectionStep('Install add-on ' . $addon . ' into tenants', array_map(function ($tenantId) use ($db, $addon) {
                return new AddonInstall($addon, $tenantId, $db);
            }, $tenantIds))
        ]);
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return $this->step->getName();
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function process(Messages $messages)
    {
        return $this->step->process($messages);
    }

    /**
     * Undo the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function undo(Messages $messages)
    {
        return $this->step->undo($messages);
    }
}
