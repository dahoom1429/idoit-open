<?php

namespace idoit\Module\System\Cleanup;

use isys_custom_fields_dao;
use isys_module_manager;

/**
 * Class RemoveOrphanedCustomCategoryData
 *
 * @package idoit\Module\System\Cleanup
 */
class RemoveOrphanedCustomCategoryData extends AbstractCleanup
{
    /**
     * Method for starting the cleanup process.
     *
     * @throws \Exception
     */
    public function process()
    {
        if (isys_module_manager::instance()->is_active('custom_fields')) {
            $dao = new isys_custom_fields_dao($this->container->get('database'));

            $count = $dao->countOrphanedData();

            if ($count) {
                if ($dao->removeOrphanedData()) {
                    echo 'Done! Deleted ' . $count . ' orphaned entries.';
                } else {
                    echo 'An error occured while removing the orphaned data: ' . $dao->get_database_component()->get_last_error_as_string();
                }
            } else {
                echo 'No orphaned entries to delete.';
            }
        }
    }
}
