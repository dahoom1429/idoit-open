<?php

/**
 * i-doit
 * CMDB UI: Global category (category type is global).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_cluster_members extends isys_cmdb_ui_category_global
{
    /**
     * Process method. Usually unreachable.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  null
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    }

    /**
     * Show the list-template for subcategories of maintenance.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return null
     * @throws isys_exception_general
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        $this->object_browser_as_new([
            'name'                                          => 'C__CATG__CLUSTER_MEMBERS__OBJ',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                ['isys_cmdb_dao_category_g_cluster_members', 'get_assigned_members'],
                $_GET[C__CMDB__GET__OBJECT]
            ]
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        parent::process_list($p_cat);
    }
}
