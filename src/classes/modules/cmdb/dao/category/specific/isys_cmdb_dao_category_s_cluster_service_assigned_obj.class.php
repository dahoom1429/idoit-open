<?php

/**
 * i-doit
 *
 * DAO: specific category for cluster services with assigned objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_cluster_service_assigned_obj extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cluster_service_assigned_obj';

    /**
     * Category's list dao name. Will be used for the list view.
     *
     * @var  string
     */
    protected $m_list = 'isys_cmdb_dao_list_cats_cluster_service_assigned_obj';

    /**
     * Method for retrieving the number of objects, assigned to an object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {

        if ($p_obj_id !== null) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT count(isys_obj__id) AS count FROM isys_obj " . "LEFT JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_catg_cluster_service_list ON isys_catg_cluster_service_list__isys_connection__id = isys_connection__id " . "WHERE TRUE " .
            "AND (isys_catg_cluster_service_list__id IS NOT NULL) ";

        if ($l_obj_id !== null) {
            $l_sql .= "AND (isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ") ";
        }

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return (int)$l_data["count"];
    }

    /**
     * Return Category Data
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_dao_cluster = new isys_cmdb_dao_category_g_cluster_service($this->m_db);

        return $l_dao_cluster->get_assigned_objects_and_relations($p_cats_list_id, $p_obj_id, $p_status);
    }

    /**
     * Method for returning the properties.
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [];
    }

    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        $l_dao = new isys_cmdb_dao_category_g_cluster_service($this->m_db);

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__ARCHIVE:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;
            case C__NAVMODE__DELETE:
                $l_status = C__RECORD_STATUS__DELETED;
                break;
            case C__NAVMODE__RECYCLE:

                if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__ARCHIVED) {
                    $l_status = C__RECORD_STATUS__NORMAL;
                } elseif (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__DELETED) {
                    $l_status = C__RECORD_STATUS__ARCHIVED;
                }
                break;
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($p_objects)) {
                    foreach ($p_objects AS $l_cat_id) {
                        $l_dao->delete($l_cat_id);
                    }
                }

                return true;
                break;

        }

        foreach ($p_objects AS $l_cat_id) {
            $l_dao->set_status($l_cat_id, $l_status);
        }

        return true;
    }

}

?>