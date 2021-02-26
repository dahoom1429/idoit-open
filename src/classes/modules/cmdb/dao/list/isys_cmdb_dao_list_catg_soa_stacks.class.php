<?php

/**
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_soa_stacks extends isys_component_dao_category_table_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__SOA_STACKS');
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
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (empty($l_cRecStatus)) {
            $l_cRecStatus = C__RECORD_STATUS__NORMAL;
        }

        return isys_cmdb_dao_category_g_soa_stacks::instance($this->m_db)
            ->get_data(null, $p_object_id, "", null, $l_cRecStatus);
    }

    public function modify_row(&$p_arrRow)
    {
        $p_arrRow["components"] = $p_arrRow["it_service"] = isys_tenantsettings::get('gui.empty_value', '-');

        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao = new isys_cmdb_dao_category_g_soa_stacks($this->get_database_component());
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_res = $l_dao->get_assigned_object($p_arrRow['isys_catg_soa_stacks_list__id']);

        if ($l_res !== false && is_countable($l_res) && count($l_res)) {
            $p_arrRow["components"] = [];

            while ($l_row = $l_res->get_row()) {
                $p_arrRow["components"][] = $l_dao_relation->get_relation_title_by_relation_data($l_row['master_title'], $l_row['slave_title'],
                    $l_row['isys_catg_relation_list__isys_relation_type__id']);
            }
        }

        $l_res = $l_dao->get_assigned_it_services($p_arrRow["isys_connection__isys_obj__id"]);

        if ($l_res !== false && is_countable($l_res) && count($l_res)) {
            $p_arrRow["it_service"] = [];

            while ($l_row = $l_res->get_row()) {
                $p_arrRow["it_service"][] = $l_quickinfo->get_quick_info($l_row["isys_obj__id"], isys_application::instance()->container->get('language')
                        ->get($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " >> " . $l_row["isys_obj__title"], C__LINK__OBJECT);
            }
        }
    }

    /**
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_soa_stacks_list__title" => "LC__CMDB__LOGBOOK__TITLE",
            "components"                       => "LC__CMDB__CATG__SOA_COMPONENTS",
            "it_service"                       => "LC__CMDB__CATG__IT_SERVICE"
        ];
    }
}
