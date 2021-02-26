<?php

/**
 * i-doit
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_database_installation extends isys_cmdb_ui_category_s_application_assigned_obj
{
    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return bool
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
        $this->list_view(
            "isys_catg_application",
            $_GET[C__CMDB__GET__OBJECT],
            isys_cmdb_dao_list_cats_database_installation::build($p_cat->get_database_component(), $p_cat)
        );

        return true;
    }
}
