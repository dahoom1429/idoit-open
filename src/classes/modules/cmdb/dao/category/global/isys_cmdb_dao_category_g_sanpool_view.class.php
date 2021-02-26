<?php

/**
 * i-doit
 *
 * DAO: Global category for storage devices
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Niclas Potthast <npotthast@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @todo       Inherit from isys_cmdb_dao_category_g_virtual because this class stores
 * no data.
 */
class isys_cmdb_dao_category_g_sanpool_view extends isys_cmdb_dao_category_g_virtual
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'sanpool_view';

    /**
     * Category's constant
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_category_const = 'C__CATG__SANPOOL';

    /**
     * Category's identifier
     *
     * @var int
     *
     * @fixme No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__SANPOOL;

    private $m_chain = [];

    private $m_exists_con_arr = [];

    private $m_exists_dev_arr = [];

    public function get_ldevserver()
    {
        $l_dao_spool = new isys_cmdb_dao_category_g_sanpool($this->get_database_component());

        $l_data_res = $l_dao_spool->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

        $l_daoClient = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());

        while ($l_ldevserver_row = $l_data_res->get_row()) {

            $l_res = $l_daoClient->get_clients($l_ldevserver_row["isys_catg_sanpool_list__id"]);

            $l_str_out3 = "";

            while ($l_row = $l_res->get_row()) {
                $l_str_out3 .= "<li class=\"bold\">" . isys_glob_str_stop($l_row["isys_obj__title"] . " >> " . $l_row["isys_catg_ldevclient_list__title"], 50) . "</li>";
            }

            $l_ldevserver_arr[] = [
                "title"         => $l_ldevserver_row["isys_catg_sanpool_list__title"],
                "lun"           => $l_ldevserver_row["isys_catg_sanpool_list__lun"],
                "capacity"      => isys_convert::formatNumber(isys_convert::memory($l_ldevserver_row["isys_catg_sanpool_list__capacity"],
                    $l_ldevserver_row["isys_memory_unit__const"], C__CONVERT_DIRECTION__BACKWARD)),
                "capacity_unit" => $l_ldevserver_row["isys_memory_unit__title"],
                "controller"    => $l_str_out3
            ];
        }

        return $l_ldevserver_arr;
    }

    public function get_ldevclient()
    {

        $l_dao_ldevclient = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());

        $l_data_res = $l_dao_ldevclient->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

        while ($l_ldevclient_row = $l_data_res->get_row()) {

            $l_ldevclient_arr[] = [
                "title"    => $l_ldevclient_row["isys_catg_ldevclient_list__title"],
                "hba"      => $l_ldevclient_row["isys_catg_hba_list__title"],
                "assigned" => $l_ldevclient_row["isys_catg_sanpool_list__title"],
                "lun"      => $l_ldevclient_row["isys_catg_sanpool_list__lun"],
            ];
        }

        return $l_ldevclient_arr;
    }

    public function get_hba()
    {

        $l_dao_hba = new isys_cmdb_dao_category_g_hba($this->get_database_component());

        $l_data_res = $l_dao_hba->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

        while ($l_hba_row = $l_data_res->get_row()) {

            $l_hba_arr[] = [
                "title"        => $l_hba_row["isys_catg_hba_list__title"],
                "type"         => $l_hba_row["isys_hba_type__title"],
                "manufacturer" => $l_hba_row["isys_controller_manufacturer__title"],
                "model"        => $l_hba_row["isys_controller_model__title"]
            ];
        }

        return $l_hba_arr;
    }

    public function get_fc_port()
    {

        $l_dao_fcport = new isys_cmdb_dao_category_g_controller_fcport($this->get_database_component());
        $l_dao_con = new isys_cmdb_dao_cable_connection($this->get_database_component());
        $l_data_res = $l_dao_fcport->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

        while ($l_fcport_row = $l_data_res->get_row()) {

            $l_connector_title = null;
            $l_cable_con_id = null;
            $l_object_connection = null;
            $l_connector_title = null;

            $l_connector_info = $l_dao_con->get_assigned_connector($l_fcport_row["isys_catg_fc_port_list__isys_catg_connector_list__id"]);
            while ($l_con_row = $l_connector_info->get_row()) {
                $l_connector_title = $l_con_row["isys_catg_connector_list__title"];
                $l_cable_con_id = $l_con_row["isys_cable_connection__id"];
            }

            if (!empty($l_cable_con_id)) {

                $l_objID = $l_dao_con->get_assigned_object($l_cable_con_id, $l_fcport_row["isys_catg_fc_port_list__isys_catg_connector_list__id"]);

                $l_objInfo = $l_dao_con->get_type_by_object_id($l_objID)
                    ->get_row();

                $l_link = isys_helper_link::create_url([
                    C__CMDB__GET__OBJECT     => $l_objID,
                    C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                    C__CMDB__GET__CATG       => defined_or_default('C__CATG__CONTROLLER_FC_PORT'),
                    C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
                ]);

                // Exchange the specified column.
                $l_object_connection = '<a href="' . $l_link . '">' . $l_objInfo["isys_obj__title"] . '</a>';

                $l_connector_title = $l_dao_con->get_assigned_connector_name($l_fcport_row["isys_catg_fc_port_list__isys_catg_connector_list__id"], $l_cable_con_id);
            }

            $l_fcport_arr[] = [
                "title"           => $l_fcport_row["isys_catg_fc_port_list__title"],
                "hba"             => $l_fcport_row["isys_catg_hba_list__title"],
                "type"            => $l_fcport_row["isys_fc_port_type__title"],
                "medium"          => $l_fcport_row["isys_fc_port_medium__title"],
                "target"          => $l_object_connection,
                "connection_type" => $l_connector_title
            ];
        }

        return $l_fcport_arr;
    }

    public function get_san_chains()
    {

        $this->set_chain_drive();

        $l_san_chain["drives"] = $this->m_chain;

        unset($this->m_chain);
        $this->set_chain_ldevclient();
        $l_san_chain["devices"] = $this->m_chain;

        return $l_san_chain;
    }

    public function set_chain_drive($p_id = null, $p_stage = null, $p_id2 = null)
    {

        $l_dao = new isys_cmdb_dao($this->get_database_component());

        if ($p_id == null) {
            $l_dao_driv = new isys_cmdb_dao_category_g_drive($this->get_database_component());
            $l_condition = " AND isys_catg_drive_list__const = 'C__CATG__LDEV_CLIENT' ";
            $l_drive_res = $l_dao_driv->get_drives(null, $_GET[C__CMDB__GET__OBJECT], $l_condition, true);

            $l_counter = 0;
            while ($l_row = $l_drive_res->get_row()) {
                $this->m_chain[$l_row["isys_catg_drive_list__id"]][] = $l_row["isys_catg_drive_list__title"];

                $this->set_chain_drive($l_row["isys_catg_drive_list__id"], 1, $l_row["isys_catg_drive_list__isys_catg_ldevclient_list__id"]);

                $l_counter++;
            }
        } else {

            switch ($p_stage) {
                // LDEV Clients
                case 1:
                    $l_sql = "SELECT * FROM isys_catg_ldevclient_list WHERE isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_id2) . " " .
                        "AND isys_catg_ldevclient_list__status = '" . C__RECORD_STATUS__NORMAL . "'";
                    $l_stor_res = $l_dao->retrieve($l_sql);
                    $l_row = $l_stor_res->get_row();

                    if ($l_stor_res->num_rows() > 0) {

                        $this->m_chain[$p_id][] .= $l_row["isys_catg_ldevclient_list__title"];
                        $this->m_exists_dev_arr[$l_row["isys_catg_ldevclient_list__id"]] = $l_row["isys_catg_ldevclient_list__id"];
                    } else {
                        $this->m_chain[$p_id][] .= "";
                    }

                    $this->set_chain_drive($p_id, 2, null);
                    break;
                // Hostbusadapter (HBA)
                case 2:

                    $l_sql = 'SELECT * FROM isys_ldevclient_fc_port_path ' . 'INNER JOIN isys_catg_fc_port_list ' .
                        'ON isys_catg_fc_port_list__id = isys_ldevclient_fc_port_path__isys_catg_fc_port_list__id ' . 'INNER JOIN isys_catg_hba_list ' .
                        'ON isys_catg_fc_port_list__isys_catg_hba_list__id = isys_catg_hba_list__id ' .
                        'WHERE isys_ldevclient_fc_port_path__isys_catg_ldevclient_list__id = ' . $this->convert_sql_id($p_id) . ' ' . 'AND isys_catg_fc_port_list__status = ' .
                        $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

                    $l_con_res = $l_dao->retrieve($l_sql);

                    if ($l_con_res->num_rows() > 0) {
                        $l_index = null;
                        while ($l_row = $l_con_res->get_row()) {

                            if (empty($l_index)) {
                                $l_index = is_countable($this->m_chain[$p_id]) ? count($this->m_chain[$p_id]) : 0;
                            }

                            $this->m_exists_con_arr[$l_row["isys_catg_hba_list__id"]] = $l_row["isys_catg_hba_list__id"];
                            $this->m_chain[$p_id][$l_index] .= $l_row["isys_catg_hba_list__title"] . ', ';
                        }
                        $this->m_chain[$p_id][$l_index] = rtrim($this->m_chain[$p_id][$l_index], ', ');

                    } else {
                        $this->m_chain[$p_id][] .= "";
                    }

                default:
                    break;
            }
        }
    }

    public function set_chain_ldevclient($p_id = null, $p_stage = null, $p_id2 = null)
    {
        $l_dao = new isys_cmdb_dao($this->get_database_component());

        if ($p_id == null) {
            $l_dao_dev = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());
            $l_dev_res = $l_dao_dev->get_clients_by_object($_GET[C__CMDB__GET__OBJECT]);

            while ($l_row = $l_dev_res->get_row()) {
                if (!in_array($l_row["isys_catg_ldevclient_list__id"], $this->m_exists_dev_arr)) {
                    $this->m_chain[$l_row["isys_catg_ldevclient_list__id"]][] = $l_row["isys_catg_ldevclient_list__title"];
                    $this->m_exists_dev_arr[$l_row["isys_catg_ldevclient_list__id"]] = $l_row["isys_catg_ldevclient_list__id"];

                    $this->set_chain_ldevclient($l_row["isys_catg_ldevclient_list__id"], 1, null);

                }
            }

        } else {

            $l_sql = 'SELECT * FROM isys_ldevclient_fc_port_path ' . 'INNER JOIN isys_catg_fc_port_list ' .
                'ON isys_catg_fc_port_list__id = isys_ldevclient_fc_port_path__isys_catg_fc_port_list__id ' . 'INNER JOIN isys_catg_hba_list ' .
                'ON isys_catg_fc_port_list__isys_catg_hba_list__id = isys_catg_hba_list__id ' . 'WHERE isys_ldevclient_fc_port_path__isys_catg_ldevclient_list__id = ' .
                $this->convert_sql_id($p_id) . ' ' . 'AND isys_catg_fc_port_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

            $l_con_res = $l_dao->retrieve($l_sql);

            if ($l_con_res->num_rows() > 0) {
                $l_index = null;
                while ($l_row = $l_con_res->get_row()) {

                    if (empty($l_index)) {
                        $l_index = is_countable($this->m_chain[$p_id]) ? count($this->m_chain[$p_id]) : 0;
                    }

                    $this->m_exists_con_arr[$l_row["isys_catg_hba_list__id"]] = $l_row["isys_catg_hba_list__id"];
                    $this->m_chain[$p_id][$l_index] .= $l_row["isys_catg_hba_list__title"] . ', ';
                }
                $this->m_chain[$p_id][$l_index] = rtrim($this->m_chain[$p_id][$l_index], ', ');
            } else {
                $this->m_chain[$p_id][] .= "";
            }
        }
    }

    public function attachObjects(array $p_post)
    {
        return null;
    }

    public function get_count($p_obj_id = null)
    {

        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT count(isys_obj__id) AS count FROM isys_obj " . "LEFT JOIN isys_catg_fc_port_list ON isys_obj__id = isys_catg_fc_port_list__isys_obj__id " .
            "LEFT JOIN isys_catg_sanpool_list ON isys_obj__id = isys_catg_sanpool_list__isys_obj__id " .
            "LEFT JOIN isys_catg_ldevclient_list ON isys_obj__id = isys_catg_ldevclient_list__isys_obj__id " .
            "LEFT JOIN isys_catg_hba_list ON isys_obj__id = isys_catg_hba_list__isys_obj__id " . "WHERE TRUE ";
        if (!empty($l_obj_id)) {
            $l_sql .= " AND (isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ") ";
        }

        $l_sql .= " AND ((isys_catg_fc_port_list__id IS NOT NULL AND isys_catg_fc_port_list__status = '" . C__RECORD_STATUS__NORMAL . "') " .
            " OR (isys_catg_sanpool_list__id IS NOT NULL AND isys_catg_sanpool_list__status = '" . C__RECORD_STATUS__NORMAL . "') " .
            " OR (isys_catg_ldevclient_list__id IS NOT NULL AND isys_catg_ldevclient_list__status = '" . C__RECORD_STATUS__NORMAL . "') " .
            " OR (isys_catg_hba_list__id != NULL AND isys_catg_hba_list__status = '" . C__RECORD_STATUS__NORMAL . "'))";

        $l_sql .= " Limit 0,1";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [];
    }

    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        return true;
    }

    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    }

}

?>