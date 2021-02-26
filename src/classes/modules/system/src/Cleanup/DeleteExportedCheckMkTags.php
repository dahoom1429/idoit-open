<?php

namespace idoit\Module\System\Cleanup;

use isys_check_mk_dao_generic_tag;
use isys_module_manager;

/**
 * Class DeleteExportedCheckMkTags
 *
 * @package idoit\Module\System\Cleanup
 */
class DeleteExportedCheckMkTags extends AbstractCleanup
{
    /**
     * Method for starting the cleanup process.
     *
     * @throws \Exception
     */
    public function process()
    {
        if (isys_module_manager::instance()->is_active('check_mk')) {
            $dao = new isys_check_mk_dao_generic_tag($this->container->get('database'));

            if ($dao->delete_exported_tags_from_database()) {
                echo 'Successfully removed all exported tags!';
            } else {
                echo 'An error occured while removing the exported tags: ' . $dao->get_database_component()->get_last_error_as_string();
            }
        }
    }
}
