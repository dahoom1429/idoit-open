<?php

/**
 * i-doit
 *
 * DAO: list for cluster members
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_cluster_service extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__CLUSTER_SERVICE');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_g_cluster_service::instance($this->m_db)
            ->get_data(null, null, " AND isys_connection__isys_obj__id = " . $p_objID, null, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    }

    public function modify_row(&$p_row)
    {
        $l_quickinfo = new isys_ajax_handler_quick_info();

        isys_cmdb_dao_list_catg_cluster_service::instance($this->m_db)
            ->modify_row($p_row);

        $p_row["application"] = $l_quickinfo->get_quick_info($p_row["isys_obj__id"], $p_row["isys_obj__title"], C__LINK__OBJECT);
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "application"              => "LC__CMDB__CATG__CLUSTER",
            "isys_cluster_type__title" => "Cluster " . isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__CATG__CLUSTER_SERVICE__TYPE"),
            "runs_on"                  => "LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON",
            "default_server"           => "LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER",
            "hostaddresses"            => "LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES"

        ];
    }
}
