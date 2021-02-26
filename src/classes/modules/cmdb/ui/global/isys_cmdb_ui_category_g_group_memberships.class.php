<?php

/**
 * i-doit
 *
 * CMDB group membership category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_group_memberships extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return null
     * @throws isys_exception_general
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $this->object_browser_as_new([
            'name'                                          => 'C__CATG__GROUP__OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__GROUP;C__CATS__GROUP_TYPE',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                ['isys_cmdb_dao_category_g_group_memberships', 'getAssignedGroups'],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    'isys_obj__id',
                    'isys_obj__title',
                    'isys_obj__isys_obj_type__id',
                    'isys_obj__sysid'
                ]
            ],
        ], 'LC__UNIVERSAL__OBJECT_ADD_REMOVE', 'LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION');

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}
