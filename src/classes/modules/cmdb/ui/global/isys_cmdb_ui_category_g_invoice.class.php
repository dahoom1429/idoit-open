<?php

/**
 * i-doit
 *
 * CMDB Invoice category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischr <lfischer@i-doit.org>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_invoice extends isys_cmdb_ui_category_global
{
    /**
     * Processes category data list for multi-valued categories.
     *
     * @param  isys_cmdb_dao_category $p_cat Category's DAO
     * @param  array                  $p_get_param_override
     * @param  string                 $p_strVarName
     * @param  string                 $p_strTemplateName
     * @param  boolean                $p_bCheckbox
     * @param  boolean                $p_bOrderLink
     * @param  string                 $p_db_field_name
     *
     * @return null
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
        // @see ID-4278

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}
