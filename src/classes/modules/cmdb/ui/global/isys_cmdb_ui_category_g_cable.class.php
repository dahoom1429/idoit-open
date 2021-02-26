<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_cable extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category $p_cat
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        // Make rules.
        $l_rules = [
            'C__CATG__CABLE_TYPE'                                                                 => [
                'p_strTable'      => 'isys_cable_type',
                'p_strSelectedID' => $l_catdata['isys_catg_cable_list__isys_cable_type__id']
            ],
            'C__CATG__CABLE_COLOUR'                                                               => [
                'p_strTable'      => 'isys_cable_colour',
                'p_strSelectedID' => $l_catdata['isys_catg_cable_list__isys_cable_colour__id']
            ],
            'C__CATG__CABLE_OCCUPANCY'                                                            => [
                'p_strTable'      => 'isys_cable_occupancy',
                'p_strSelectedID' => $l_catdata['isys_catg_cable_list__isys_cable_occupancy__id']
            ],
            'C__CATG__CABLE_MAX_AMOUNT_OF_FIBERS_LEADS'                                           => [
                'p_strValue' => $l_catdata['isys_catg_cable_list__max_amount_of_fibers_leads']
            ],
            'C__CATG__CABLE_LENGTH_UNIT'                                                          => [
                'p_strSelectedID' => $l_catdata['isys_catg_cable_list__isys_depth_unit__id']
            ],
            'C__CATG__CABLE_LENGTH'                                                               => [
                'p_strValue' => $this->formatNumberViewMode(isys_convert::measure($l_catdata['isys_catg_cable_list__length'], $l_catdata['isys_catg_cable_list__isys_depth_unit__id'],
                    C__CONVERT_DIRECTION__BACKWARD))
            ],
            'C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id() => [
                'p_strValue' => $l_catdata["isys_catg_cable_list__description"]
            ]
        ];

        // Apply rules.
        $this->get_template_component()
            ->include_template('contentbottomcontent', $this->get_template())
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}