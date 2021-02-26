<?php

/**
 * i-doit
 *
 * Export helper for global category hostaddress
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_accounting_export_helper extends isys_export_helper
{
    /**
     * @param $p_id
     *
     * @return mixed
     */
    public function get_guarantee_status($p_id)
    {
        $l_dao = isys_cmdb_dao_category_g_accounting::instance($this->m_database);

        $l_data = $l_dao->get_data($p_id)
            ->get_row();

        $l_date = false;

        switch ($l_data['isys_catg_accounting_list__guarantee_period_base']) {
            case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DELIVERY_DATE:
                $l_date = strtotime($l_data['isys_catg_accounting_list__delivery_date']);
                break;
            case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__ORDER_DATE:
                $l_date = strtotime($l_data['isys_catg_accounting_list__order_date']);
                break;
            case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE:
                $l_date = strtotime($l_data['isys_catg_accounting_list__acquirementdate']);
                break;
            default:
                break;
        }

        if ($l_date === false) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $l_dao->calculate_guarantee_status(
            $l_date,
            $l_data['isys_catg_accounting_list__guarantee_period'],
            $l_data['isys_catg_accounting_list__isys_guarantee_period_unit__id']
        );
    }

    /**
     * Import method for guarantee status.
     *
     * @return  boolean
     */
    public function get_guarantee_status_import()
    {
        return null;
    }
}
