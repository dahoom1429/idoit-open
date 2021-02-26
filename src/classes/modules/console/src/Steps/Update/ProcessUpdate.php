<?php

namespace idoit\Module\Console\Steps\Update;

use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\FileSystem\DeleteFiles;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\IfCheck;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\TemplateFile;
use idoit\Module\Console\Steps\Undoable;
use isys_component_dao_mandator;
use isys_component_database;
use isys_update;

/**
 * Class CopyUpdateFiles
 */
class ProcessUpdate implements Step, Undoable
{
    /**
     * @var CollectionStep
     */
    private $updateStep;

    /**
     * @param isys_component_database $systemDatabase
     *
     * @return array
     * @throws \Exception
     */
    private function getTenantDatabases(isys_component_database $systemDatabase)
    {
        $sql = 'SELECT *
			FROM isys_mandator
			WHERE isys_mandator__active = 1
			GROUP BY isys_mandator__db_name;';

        $result = $systemDatabase->query($sql);

        $databases = [];

        while ($connectionInfo = $systemDatabase->fetch_row_assoc($result)) {
            $id = $connectionInfo['isys_mandator__id'];

            $databases[$id] = isys_component_database::get_database(
                'mysqli',
                $connectionInfo['isys_mandator__db_host'],
                $connectionInfo['isys_mandator__db_port'],
                $connectionInfo['isys_mandator__db_user'],
                isys_component_dao_mandator::getPassword($connectionInfo),
                $connectionInfo['isys_mandator__db_name']
            );
        }

        return $databases;
    }

    /**
     * FileCopy constructor.
     *
     * @param string                  $updatePath
     * @param isys_component_database $systemDatabase
     */
    public function __construct(string $updatePath, isys_component_database $systemDatabase)
    {
        global $g_absdir, $g_temp_dir;

        $databases = $this->getTenantDatabases($systemDatabase);

        $steps = [
            new UpdateDatabase($updatePath . '/' . C__XML__SYSTEM, $systemDatabase, true),
            new CollectionStep('Update tenant databases', array_map(function ($database) use ($updatePath) {
                return new UpdateDatabase($updatePath . '/' . C__XML__DATA, $database, true);
            }, $databases)),
            new RemoveUpdateFiles($updatePath),
        ];

        $proAddonPath = $g_absdir . '/src/classes/modules/pro/install/';

        if (file_exists($proAddonPath . 'update_sys.xml')) {
            $steps [] = new UpdateDatabase($proAddonPath . 'update_sys.xml', $systemDatabase, false);
        }

        if (file_exists($proAddonPath . 'update_data.xml')) {
            $steps [] = new CollectionStep('Update tenant databases', array_map(function ($database) use ($proAddonPath) {
                return new UpdateDatabase($proAddonPath . 'update_data.xml', $database, false);
            }, $databases));
        }

        $steps [] = new CollectionStep('Migrate tenant databases', array_map(function ($database) use ($updatePath) {
            return new MigrateDatabase($updatePath . '/' . C__DIR__MIGRATION, $database, false);
        }, $databases));

        $toRemove = [
            new DeleteFiles('Temp files', $g_temp_dir),
        ];

        if (is_dir($g_absdir . '/src/themes/default/smarty/cache')) {
            $toRemove[] = new DeleteFiles('Smarty Cache', $g_absdir . '/src/themes/default/smarty/cache');
        }

        if (is_dir($g_absdir . '/src/themes/default/smarty/templates_c')) {
            $toRemove[] = new DeleteFiles('Smarty Compiled', $g_absdir . '/src/themes/default/smarty/templates_c');
        }

        $steps[] = new CollectionStep('Clear caches', $toRemove);

        $steps[] = new EmitSignalStep('System Changed', 'system.afterChange');

        $this->updateStep = new CollectionStep('Process update', $steps);
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return $this->updateStep->getName();
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return bool
     */
    public function process(Messages $messages)
    {
        return $this->updateStep->process($messages);
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
        return $this->updateStep->undo($messages);
    }
}
