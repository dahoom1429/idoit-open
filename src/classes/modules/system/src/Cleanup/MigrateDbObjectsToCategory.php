<?php

namespace idoit\Module\System\Cleanup;

use idoit\Context\Context;
use isys_custom_fields_dao;
use isys_migration_dao_database_objects_to_category;
use isys_module_manager;
use isys_module_migration;

/**
 * Class MigrateDbObjectsToCategory
 *
 * @package idoit\Module\System\Cleanup
 */
class MigrateDbObjectsToCategory extends AbstractCleanup
{
    /**
     * Method for starting the cleanup process.
     *
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     */
    public function process()
    {
        /**
         * @var $dao isys_migration_dao_database_objects_to_category
         */
        $dao = isys_module_migration::getMigrationDao($this->container->get('database'), 'isys_migration_dao_database_objects_to_category');

        Context::instance()
            ->setOrigin(Context::ORIGIN_GUI)
            ->setGroup(Context::CONTEXT_MIGRATION)
            ->setContextCustomer(Context::CONTEXT_MIGRATION_BY_USER);

        if ($dao->migrationAlreadyExecuted() === true) {
            echo "Migration already done!";
        } else {
            $dao->executeMigration();

            echo "Migration has been executed.";

            $dao->deactivateMigration();
            $dao->addMigrationEntry(
                $dao->getMigrationTitle(),
                $this->container->get('session')->get_current_username(),
                Context::CONTEXT_MIGRATION_BY_USER
            );
        }
    }
}
