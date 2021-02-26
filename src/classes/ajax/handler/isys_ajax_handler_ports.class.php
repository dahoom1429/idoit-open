<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_ports extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $return = [];

        switch ($_GET['func']) {
            case 'load_port_overview':
                $return = $this->get_port_overview($_POST[C__CMDB__GET__OBJECT]);
                break;

            case 'load_fc_ports':
                $return = $this->get_fc_ports(isys_format_json::decode($_POST[C__CMDB__GET__OBJECT]), $_POST[C__CMDB__GET__CATLEVEL]);
                break;
        }

        echo isys_format_json::encode($return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return bool
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * @param int $objectId
     *
     * @return array
     */
    public function get_port_overview($objectId)
    {
        return isys_cmdb_dao_category_g_network_port_overview::instance($this->m_database_component)
            ->get_port_overview($objectId);
    }

    /**
     * @param array $objectIds
     * @param int   $categoryEntryId
     *
     * @return array
     */
    public function get_fc_ports($objectIds = [], $categoryEntryId)
    {
        if (!is_array($objectIds) && empty($objectIds)) {
            $objectIds = [];
        }

        return isys_cmdb_dao_category_g_controller_fcport::instance($this->m_database_component)
            ->prepare_data_for_gui($objectIds, $categoryEntryId);
    }
}
