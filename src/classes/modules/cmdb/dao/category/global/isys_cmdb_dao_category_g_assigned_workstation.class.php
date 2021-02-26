<?php

/**
 * i-doit
 *
 * DAO: logical unit extension: assigned workstation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_assigned_workstation extends isys_cmdb_dao_category_g_logical_unit
{
    protected $m_category_const = 'C__CATG__ASSIGNED_WORKSTATION';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__ASSIGNED_WORKSTATION';

    /**     * Method for retrieving the category UI class.
     *
     * @return  isys_cmdb_ui_category_g_assigned_workstation
     */
    public function &get_ui()
    {
        return new isys_cmdb_ui_category_g_assigned_workstation(isys_application::instance()->template);
    }

    /**
     * Method for returning the properties. Unused because reverse category.
     * Why is it unused? We need the properties to use all necessary generic functions,
     * otherwise all important functions must be defined in this class.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        $l_properties = isys_cmdb_dao_category_g_logical_unit::properties();

        /**
         * @var idoit\Module\Report\SqlQuery\Structure\SelectSubSelect $l_selectSubSelect
         */
        $l_selectSubSelect = $l_properties['parent'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $l_selectCondition = $l_selectSubSelect->setSelectLimit(1)->getSelectCondition();
        $l_selectCondition->setCondition([
            'isys_obj__isys_obj_type__id IN
                                (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
                                    INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                                    WHERE isysgui_catg__const = \'C__CATG__ASSIGNED_LOGICAL_UNIT\')'
        ]);
        $l_properties['parent'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] = $l_selectSubSelect->setSelectCondition($l_selectCondition);
        $parentUiParams = $l_properties['parent'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
        $parentUiParams[isys_popup_browser_object_ng::C__CAT_FILTER] = 'C__CATG__ASSIGNED_LOGICAL_UNIT';
        $l_properties['parent'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS] = $parentUiParams;
        $l_properties['description'][C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_WORKSTATION', 'C__CATG__ASSIGNED_WORKSTATION');

        return $l_properties;
    }
}
