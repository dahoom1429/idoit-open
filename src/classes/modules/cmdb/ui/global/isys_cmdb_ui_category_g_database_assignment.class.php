<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_database_assignment extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for specific category monitor.
     *
     * @param   isys_cmdb_dao_category_g_database_assignment $p_cat
     *
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_rules = [
            'C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT'                                             => [
                'p_strValue' => $l_catdata['assigned_obj_id']
            ],
            'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_ASSIGNMENT', 'C__CATG__DATABASE_ASSIGNMENT') => [
                'p_strValue' => $l_catdata['isys_cats_database_access_list__description']
            ]
        ];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
