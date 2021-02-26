<?php

/**
 * i-doit
 * DAO: specific category list for fiber/lead
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_fiber_lead extends isys_component_dao_category_table_list
{
    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_fiber_lead_list__id'      => 'ID',
            'isys_catg_fiber_lead_list__label'   => isys_application::instance()->container->get('language')
                ->get('LC__CATG__FIBER_LEAD__LABEL'),
            'isys_fiber_category__title'         => isys_application::instance()->container->get('language')
                ->get('LC__CATG__FIBER_LEAD__CATEGORY'),
            'isys_cable_colour__title'           => isys_application::instance()->container->get('language')
                ->get('LC__CATG__FIBER_LEAD__COLOR'),
            'isys_catg_fiber_lead_list__damping' => isys_application::instance()->container->get('language')
                ->get('LC__CATG__FIBER_LEAD__DAMPING')
        ];
    }

    /**
     * Modify row method will be called for each row to alter its content.
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        if ($p_row['isys_catg_fiber_lead_list__damping'] > 0) {
            $p_row['isys_catg_fiber_lead_list__damping'] .= ' DB';
        } else {
            $p_row['isys_catg_fiber_lead_list__damping'] = isys_tenantsettings::get('gui.empty_value', '-');
        }
    }
}
