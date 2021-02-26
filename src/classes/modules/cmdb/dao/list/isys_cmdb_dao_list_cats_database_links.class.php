<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_database_links extends isys_component_dao_category_table_list
{
    /**
     * @return integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__DATABASE_LINKS');
    }

    /**
     * @return integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["isys_cats_database_links_list__public"] = ($p_row["isys_cats_database_links_list__public"]) ? isys_application::instance()->container->get('language')
            ->get('LC__UNIVERSAL__YES') : isys_application::instance()->container->get('language')
            ->get('LC__UNIVERSAL__NO');
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_database_links_list__title"       => "LC__UNIVERSAL__TITLE",
            "schema_title"                               => "LC__OBJTYPE__DATABASE_SCHEMA",
            "isys_cats_database_links_list__target_user" => "LC__CMDB__CATS__DATABASE_LINKS__TARGET_USER",
            "isys_cats_database_links_list__owner"       => "LC__CMDB__CATS__DATABASE_LINKS__OWNER",
            "isys_cats_database_links_list__public"      => "LC__UNIVERSAL__PUBLIC"
        ];
    }
}
