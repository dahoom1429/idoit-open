<?php

/**
 * i-doit
 *
 * CMDB person assigned workstation: global category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_person_assigned_workstation extends isys_cmdb_ui_category_global
{
    /**
     * Name of the used template.
     *
     * @var  string
     */
    protected $m_template = 'catg__person_assigned_workstation.tpl';

    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_person_assigned_workstation $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_dao_res = $p_cat->get_selected_objects($_GET[C__CMDB__GET__OBJECT]);

        $l_selected_workstation = [];
        if ($l_dao_res->num_rows() > 0) {
            while ($l_dao_row = $l_dao_res->get_row()) {
                $l_selected_workstation[] = $l_dao_row['isys_catg_logical_unit_list__isys_obj__id'];
            }
        }

        // Make rules.
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata['isys_catg_logical_unit_list__description'];
        $l_rules['C__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION']['p_strSelectedID'] = implode(',', $l_selected_workstation);
        $l_rules['C__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION']['dataretrieval'] = isys_format_json::encode([
            [
                get_class($p_cat),
                'get_selected_objects'
            ],
            $_GET[C__CMDB__GET__OBJECT],
            [
                'isys_obj__id',
                'isys_obj__title',
                'isys_obj__isys_obj_type__id',
                'isys_obj__sysid'
            ]
        ]);

        // Add the rules to the template.
        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = $this->activate_commentary($p_cat)
            ->get_template();
    }
}
