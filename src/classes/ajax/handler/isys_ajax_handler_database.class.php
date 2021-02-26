<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_database extends isys_ajax_handler
{
    /**
     * Init method for this request.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try {
            switch ($_GET['func']) {
                case 'getApplicationData':
                    $dao = isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'));
                    $return['data'] = $dao->getApplicationData($_POST['objectId'], $_POST['applicationId']);
                    break;
                case 'loadHierarchy':
                    $dao = isys_cmdb_dao_category_g_database_folder::instance(isys_application::instance()->container->get('database'));
                    $return['data'] = $dao->getNodeHierarchy($_POST['Id']);
                    break;
                case 'getDatabaseInstances':
                    $dao = isys_cmdb_dao_category_g_database_sa::instance(isys_application::instance()->container->get('database'));
                    $return['data'] = $dao->getDbmsInstancesByApplicationId($_POST['applicationId']);
                    break;
                case 'getDatabaseSchemas':
                    $dao = isys_cmdb_dao_category_g_database_table::instance(isys_application::instance()->container->get('database'));
                    $return['data'] = $dao->getDatabaseSchemas($_POST['databaseId']);
                    break;
            }
        } catch (Exception $e) {
            $return['success'] = false;
            $return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($return);

        $this->_die();
    }

    /**
     * Define, if this ajax request needs the hypergate logic.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }
}
