<?php

namespace idoit\Module\Console\Steps\Addon;

use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\Dao\TenantExistById;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Sql\ExecuteQueryStep;
use idoit\Module\Console\Steps\Step;
use isys_component_constant_manager;
use isys_component_dao_mandator;
use isys_component_database;
use isys_module_manager;
use isys_update_files;
use isys_update_xml;

class AddonInstall implements Step
{
    private $id;

    /**
     * @var isys_module_manager
     */
    private $moduleManager;

    /**
     * @var isys_component_database
     */
    private $systemDb;

    /**
     * @var isys_component_database
     */
    private $tenantDb;

    /**
     * @var int Id of the tenant
     */
    private $tenantId;

    /**
     * @var array|Step[] Steps to undo
     */
    private $toUndo = [];

    private $installed = false;

    public function __construct($id, $tenantId, isys_component_database $systemDb)
    {
        $this->id = $id;
        $this->tenantId = $tenantId;
        $this->systemDb = $systemDb;

        $tenantDao = isys_component_dao_mandator::instance($this->systemDb);
        $tenant = $tenantDao->get_mandator($this->tenantId, 0)
            ->get_row();

        if (!is_array($tenant)) {
            return;
        }

        $this->tenantDb = isys_component_database::get_database(
            'mysql',
            $tenant["isys_mandator__db_host"],
            $tenant["isys_mandator__db_port"],
            $tenant["isys_mandator__db_user"],
            isys_component_dao_mandator::getPassword($tenant),
            $tenant["isys_mandator__db_name"]
        );
        $this->moduleManager = new isys_module_manager($this->tenantDb);
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Install Addon ' . $this->id . ' for tenant ' . $this->tenantId;
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
        $this->toUndo = [];
        $this->installed = false;

        global $g_absdir;
        $targetPath = $g_absdir . '/src/classes/modules/' . $this->id;
        $packageFile = $targetPath . '/package.json';

        if (!file_exists($packageFile)) {
            $messages->addMessage(new StepMessage($this, 'Package file for add-on ' . $this->id . ' is not found', ErrorLevel::ERROR));

            return false;
        }

        $package = json_decode(file_get_contents($packageFile), true);

        // todo: check requirements of the package

        try {
            // Include module installscript if available.
            if (file_exists($targetPath . '/install/isys_module_' . $this->id . '_install.class.php')) {
                include_once($targetPath . '/install/isys_module_' . $this->id . '_install.class.php');
            }

            // Delete files if necessary.
            if (file_exists($targetPath . '/install/update_files.xml')) {
                (new isys_update_files())->delete($targetPath . '/install');
            }

            $id = $this->moduleManager->installAddOn($package);
            $this->installed = true;

            if ($id === false) {
                $messages->addMessage(new StepMessage($this, 'Add-on ' . $this->id . ' is not installed for tenant ' . $this->tenantId, ErrorLevel::ERROR));

                return false;
            }

            $messages->addMessage(new StepMessage($this, 'Add-on ' . $this->id . ' is installed for tenant ' . $this->tenantId, ErrorLevel::INFO));

            $step = new CollectionStep('Update databases', [
                new Updater($targetPath . '/install/update_data.xml', $this->tenantDb),
                new Updater($targetPath . '/install/update_sys.xml', $this->systemDb)
            ]);

            if ($step->process($messages)) {
                $this->toUndo[] = $step;
            } else {
                return false;
            }

            // When a package.json already exists, this is an update.
            if (file_exists($packageFile)) {
                $type = 'update';
            } else {
                $type = 'install';
            }

            $moduleClassName = 'isys_module_' . $this->id;
            $updateSettings = false;

            if (class_exists($moduleClassName) && is_a($moduleClassName, 'idoit\AddOn\InstallableInterface', true)) {
                $moduleClassName::install($this->tenantDb, $this->systemDb, $this->id, $type, $this->tenantId);
                $updateSettings = true;
            } else {
                // Call module installscript if available.
                $installClass = 'isys_module_' . $this->id . '_install';

                if (class_exists($installClass)) {
                    call_user_func([$installClass, 'init'], $this->tenantDb, $this->systemDb, $id, $type, $this->tenantId);

                    $updateSettings = true;
                }
            }

            if ($updateSettings) {
                $step = new CollectionStep('Update settings', [
                    new ExecuteQueryStep($this->systemDb, "REPLACE INTO isys_settings SET 
                        isys_settings__key = 'admin.module." . $this->id . ".installed', 
                        isys_settings__value = '" . time() . "', 
                        isys_settings__isys_mandator__id = '" . $this->tenantId . "';"),
                    new ExecuteQueryStep($this->systemDb, "REPLACE INTO isys_settings SET 
                        isys_settings__key = 'cmdb.renew-properties', 
                        isys_settings__value = 1, 
                        isys_settings__isys_mandator__id = '" . $this->tenantId . "';")
                ]);

                if ($step->process($messages)) {
                    $this->toUndo[] = $step;
                } else {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            $messages->addMessage(new StepMessage($this, $e->getMessage(), ErrorLevel::ERROR));
        }

        return false;
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
        $result = true;

        if ($this->installed) {
            $errors = [];
            $result = $this->moduleManager->uninstallAddOn($this->id, [], $errors);

            foreach ($errors as $error) {
                $messages->addMessage(new StepMessage($this, $error, ErrorLevel::NOTIFICATION));
            }
        }

        foreach ($this->toUndo as $item) {
            $result &= $item->undo($messages);
        }

        return $result;
    }
}
