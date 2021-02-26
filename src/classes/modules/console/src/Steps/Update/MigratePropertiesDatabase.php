<?php

namespace idoit\Module\Console\Steps\Update;

use Exception;
use idoit\Module\Cmdb\Model\CiTypeCategoryAssigner;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use isys_cmdb_dao_object_type;
use isys_component_dao;
use isys_component_database;
use isys_log;
use isys_log_migration;
use isys_update;
use isys_update_property_migration;

/**
 * Class MigratePropertiesDatabase
 */
class MigratePropertiesDatabase implements Step
{
    /**
     * @var isys_component_database
     */
    private $database;

    /**
     * @var bool
     */
    private $doVersionChange;

    /**
     * @var string
     */
    private $updateFile;

    /**
     * MigratePropertiesDatabase constructor.
     *
     * @param isys_component_database $database
     */
    public function __construct(isys_component_database $database)
    {
        $this->database = $database;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Migrate properties `' . $this->database->get_db_name() . '`` with ' . $this->updateFile;
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
        global $g_absdir;

        $migration = new isys_update_property_migration();

        try {
            $messages->addMessage(new StepMessage($this, 'Migrate', ErrorLevel::INFO));

            $migration->set_database($this->database)
                ->reset_property_table()
                ->collect_category_data()
                ->prepare_sql_queries('g')
                ->prepare_sql_queries('s')
                ->prepare_sql_queries('g_custom')
                ->execute_sql()
                ->get_results();

            $messages->addMessage(new StepMessage($this, 'Refresh object list', ErrorLevel::INFO));

            isys_cmdb_dao_object_type::instance($this->database)
                ->refresh_objtype_list_config(null, true);

            try {
                $messages->addMessage(new StepMessage($this, 'Set default categories', ErrorLevel::INFO));

                CiTypeCategoryAssigner::factory($this->database)
                    ->setAllCiTypes()
                    ->setDefaultCategories()
                    ->assign();
            } catch (Exception $e) {
                $messages->addMessage(new StepMessage($this, 'Set default categories: failed', ErrorLevel::NOTIFICATION));
            }
        } catch (Exception $exception) {
            $messages->addMessage(new StepMessage($this, 'Migration failed', ErrorLevel::ERROR));

            return false;
        }

        $messages->addMessage(new StepMessage($this, 'Migration is successful', ErrorLevel::INFO));

        return true;
    }
}
