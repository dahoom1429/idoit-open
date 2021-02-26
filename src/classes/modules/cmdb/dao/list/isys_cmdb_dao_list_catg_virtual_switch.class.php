<?php

/**
 * i-doit
 *
 * DAO: Gloabl category Hostadapter (HBA)
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_virtual_switch extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__VIRTUAL_SWITCH');
    }

    /**
     * Return constant of category type
     *
     * @return integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    public function modify_row(&$p_arRow)
    {
        $l_pgs = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_db)
            ->get_port_groups($p_arRow["isys_catg_virtual_switch_list__id"]);

        $p_arRow["port_groups"] = isys_tenantsettings::get('gui.empty_value', '-');

        if (is_countable($l_pgs) && count($l_pgs)) {
            $p_arRow["port_groups"] = [];

            while ($l_row = $l_pgs->get_row()) {
                $p_arRow["port_groups"][] = $l_row["isys_virtual_port_group__title"];
            }
        }
    }

    /**
     * Returns array with table headers
     *
     * @return array
     */
    public function get_fields()
    {
        $languageManager = isys_application::instance()->container->get('language');

        return [
            "isys_catg_virtual_switch_list__title" => $languageManager->get("LC__CMDB__CATG__TITLE"),
            "port_groups"                          => $languageManager->get("LC__CMDB__CATG__VSWITCH__PORT_GROUPS")
        ];
    }
}
