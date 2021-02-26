<?php

/**
 * i-doit.
 *
 * DAO: Category list for passwords.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_password extends isys_component_dao_category_table_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__PASSWD');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param  array $p_aRow
     */
    public function modify_row(&$p_aRow)
    {
        if (!empty($p_aRow['isys_catg_password_list__password'])) {
            $p_aRow['isys_catg_password_list__password'] = isys_helper_crypt::decrypt($p_aRow['isys_catg_password_list__password']);
        }

        $p_aRow['isys_catg_password_list__password'] = isys_glob_htmlentities($p_aRow['isys_catg_password_list__password']);
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_password_list__id"       => "ID",
            "isys_catg_password_list__title"    => "LC__CMDB__CATG__TITLE",
            "isys_catg_password_list__username" => "LC__LOGIN__USERNAME",
            "isys_catg_password_list__password" => "LC__LOGIN__PASSWORD"
        ];
    }
}