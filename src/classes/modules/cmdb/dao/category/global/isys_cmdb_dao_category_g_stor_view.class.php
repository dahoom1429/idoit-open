<?php

/**
 * i-doit
 *
 * DAO: Global category for storage devices
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Niclas Potthast <npotthast@i-doit.org> - 2006-03-06
 * @version    Andre Wösten <awoesten@i-doit.org> - 2006-08-08
 * @version    Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_stor_view extends isys_cmdb_dao_category_g_virtual
{

    private $m_chain = [];

    private $m_exists_con_arr = [];

    private $m_exists_dev_arr = [];

    /**
     * @return int
     */
    public function get_category_id()
    {
        return defined_or_default('C__CATG__STORAGE');
    }

    /**
     * @param int|null $p_obj_id
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id > 0) {
            $l_sql = 'SELECT COUNT(*) AS cnt FROM (
						(SELECT isys_catg_stor_list__id AS id FROM isys_catg_stor_list
							WHERE isys_catg_stor_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
							AND isys_catg_stor_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' LIMIT 0,1) UNION ' . '(SELECT isys_catg_controller_list__id AS id FROM isys_catg_controller_list
						WHERE isys_catg_controller_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
						AND isys_catg_controller_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' LIMIT 0,1) UNION ' . '(SELECT isys_catg_raid_list__id AS id FROM isys_catg_raid_list
					 	WHERE isys_catg_raid_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
						AND isys_catg_raid_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' LIMIT 0,1)) AS CNTLIST;';

            return $this->retrieve($l_sql)
                ->get_row_value('cnt');
        }

        return 0;
    }

    /**
     * @return isys_cmdb_ui_category_g_network_interface
     */
    public function &get_ui()
    {
        return new isys_cmdb_ui_category_g_stor_view(isys_application::instance()->template);
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

    /**
     * @param array  $p_objects
     * @param string $p_direction
     * @param string $p_table
     *
     * @return bool
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        return true;
    }

    /**
     * @param int $p_cat_level
     * @param int $p_intOldRecStatus
     *
     * @return null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function get_devices()
    {
        $l_dao_stor = new isys_cmdb_dao_category_g_stor($this->get_database_component());

        $l_devices_res = $l_dao_stor->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_devices_res->get_row()) {
            $l_devices[$l_row["isys_catg_stor_list__id"]] = [
                "title"         => $l_row["isys_catg_stor_list__title"],
                "typ"           => $l_row["isys_stor_type__title"],
                "capacity"      => isys_convert::formatNumber(isys_convert::memory($l_row["isys_catg_stor_list__capacity"],
                    $l_row["isys_catg_stor_list__isys_memory_unit__id"], C__CONVERT_DIRECTION__BACKWARD)),
                "capacity_unit" => $l_row["isys_memory_unit__title"],
                "controller"    => $l_row["isys_catg_controller_list__title"]
            ];
        }

        return $l_devices;
    }

    /**
     * @return mixed
     */
    public function get_controllers()
    {

        $l_dao_con = new isys_cmdb_dao_category_g_controller($this->get_database_component());

        $l_con_res = $l_dao_con->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_con_res->get_row()) {
            $l_controllers[$l_row["isys_catg_controller_list__id"]] = [
                "title"        => $l_row["isys_catg_controller_list__title"],
                "typ"          => $l_row["isys_controller_type__title"],
                "manufacturer" => $l_row["isys_controller_manufacturer__title"],
                "modell"       => $l_row["isys_controller_model__title"]
            ];
        }

        return $l_controllers;
    }

    /**
     * @return mixed
     */
    public function get_raids()
    {
        $l_dao_raid = new isys_cmdb_dao_category_g_raid($this->get_database_component());
        $l_dao_stor = new isys_cmdb_dao_category_g_stor($this->get_database_component());
        $l_dao_drive = new isys_cmdb_dao_category_g_drive($this->get_database_component());

        $l_raid_res = $l_dao_raid->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_raid_res->get_row()) {
            $l_min_capacity = null;
            $l_num_disks = $l_total_capacity = 0;

            if ($l_row["isys_catg_raid_list__isys_raid_type__id"] == 1) {
                $l_raid_id = $l_row["isys_catg_raid_list__id"];
                $l_res = $l_dao_stor->get_devices(null, $_GET[C__CMDB__GET__OBJECT], $l_raid_id);

                $l_num_disks = $l_res->num_rows();

                if ($l_res && $l_num_disks) {
                    while ($l_row2 = $l_res->get_row()) {
                        if ($l_row2["isys_catg_stor_list__hotspare"] == "1") {
                            $l_num_disks--;
                        }

                        if ($l_row2["isys_catg_stor_list__capacity"] <= $l_min_capacity ||
                            !isset($l_min_capacity)) {
                            $l_min_capacity = $l_row2["isys_catg_stor_list__capacity"];
                        }

                        $l_total_capacity += $l_row2["isys_catg_stor_list__capacity"];
                    }
                }
            }
            if ($l_row["isys_catg_raid_list__isys_raid_type__id"] == 2) {
                $l_res = $l_dao_drive->get_drives($l_row["isys_catg_raid_list__id"], $_GET[C__CMDB__GET__OBJECT]);

                $l_num_disks = $l_res->num_rows();

                if ($l_res->num_rows() > 0) {
                    while ($l_row2 = $l_res->get_row()) {
                        $l_driveSelected[$l_row2["isys_catg_drive_list__id"]] = $l_row2["isys_catg_drive_list__title"];

                        if ($l_row2["isys_catg_drive_list__capacity"] <= $l_min_capacity ||
                            !isset($l_min_capacity)) {
                            $l_min_capacity = $l_row2["isys_catg_drive_list__capacity"];
                        }

                        $l_total_capacity += $l_row2["isys_catg_drive_list__capacity"];
                    }
                }
            }

            $l_unit = isys_convert::get_memory_unit_const($l_total_capacity, true);

            switch ($l_unit) {
                case 'C__MEMORY_UNIT__TB':
                    $l_memory_type_const = 'LC__CMDB__MEMORY_UNIT__TB';
                    break;
                case 'C__MEMORY_UNIT__KB':
                    $l_memory_type_const = 'LC__CMDB__MEMORY_UNIT__KB';
                    break;
                case 'C__MEMORY_UNIT__MB':
                    $l_memory_type_const = 'LC__CMDB__MEMORY_UNIT__MB';
                    break;
                case 'C__MEMORY_UNIT__GB':
                    $l_memory_type_const = 'LC__CMDB__MEMORY_UNIT__GB';
                    break;
                default:
                    $l_memory_type_const = 'LC__CMDB__MEMORY_UNIT__Bytes';
                    break;
            }

            if ($l_row["isys_stor_raid_level__const"] == "C__STOR_RAID_LEVEL__JBOD") {
                $l_capacity = $l_total_capacity;
            } else {
                $l_capacity = $l_dao_stor->raidcalc($l_num_disks, $l_min_capacity, $l_row["isys_stor_raid_level__title"]);
            }

            $l_raid[$l_row["isys_catg_raid_list__id"]] = [
                "title"         => $l_row["isys_catg_raid_list__title"],
                "typ"           => $l_row["isys_raid_type__title"],
                "raid_level"    => $l_row["isys_stor_raid_level__title"],
                "capacity"      => isys_convert::memory($l_capacity, $l_unit, C__CONVERT_DIRECTION__BACKWARD),
                "capacity_unit" => isys_application::instance()->container->get('language')->get($l_memory_type_const)
            ];

        }

        return $l_raid;
    }

    /**
     * @return mixed
     */
    public function get_das_chains()
    {

        $l_dao_driv = new isys_cmdb_dao_category_g_drive($this->get_database_component());
        $l_dao_dev = new isys_cmdb_dao_category_g_stor($this->get_database_component());
        $l_dao_con = new isys_cmdb_dao_category_g_controller($this->get_database_component());
        $l_dao_raid = new isys_cmdb_dao_category_g_raid($this->get_database_component());
        $l_dao_hba = new isys_cmdb_dao_category_g_hba($this->get_database_component());

        unset($this->m_chain);
        $this->set_chain_drive();
        $l_das_chain["drives"] = $this->m_chain;

        unset($this->m_chain);
        $this->set_chain_devices();
        $l_das_chain["devices"] = $this->m_chain;

        return $l_das_chain;
    }

    /**
     * @param null $p_id
     * @param null $p_stage
     * @param null $p_f_id
     *
     * @throws isys_exception_database
     */
    public function set_chain_devices($p_id = null, $p_stage = null, $p_f_id = null)
    {
        $l_dao = new isys_cmdb_dao($this->get_database_component());

        if ($p_id == null) {
            $l_dao_dev = new isys_cmdb_dao_category_g_stor($this->get_database_component());
            $l_dev_res = $l_dao_dev->get_devices(null, $_GET[C__CMDB__GET__OBJECT], null, null, null, true, C__RECORD_STATUS__NORMAL);

            while ($l_row = $l_dev_res->get_row()) {
                if (!in_array($l_row["isys_catg_stor_list__id"], $this->m_exists_dev_arr) && $l_row["isys_catg_stor_list__status"] == C__RECORD_STATUS__NORMAL) {
                    $this->m_chain[$l_row["isys_catg_stor_list__id"]][] = $l_row["isys_catg_stor_list__title"];
                    $this->m_exists_dev_arr[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__id"];

                    $this->set_chain_devices($l_row["isys_catg_stor_list__id"], 2, $l_row["isys_catg_stor_list__isys_catg_raid_list__id"]);
                    $this->set_chain_devices($l_row["isys_catg_stor_list__id"], 1, $l_row["isys_catg_stor_list__isys_catg_controller_list__id"]);

                }
            }

        } else {
            switch ($p_stage) {
                case 1:
                    $l_sql = "SELECT * FROM isys_catg_controller_list WHERE isys_catg_controller_list__id = " . $this->convert_sql_id($p_f_id) . " " .
                        "AND isys_catg_controller_list__status = '" . C__RECORD_STATUS__NORMAL . "'";
                    $l_con_res = $l_dao->retrieve($l_sql);

                    $l_row = $l_con_res->get_row();
                    if ($l_con_res->num_rows() > 0) {
                        $this->m_exists_con_arr[$l_row["isys_catg_controller_list__id"]] = $l_row["isys_catg_controller_list__id"];
                        $this->m_chain[$p_id][] .= $l_row["isys_catg_controller_list__title"];
                    } else {
                        $this->m_chain[$p_id][] .= "";
                    }
                    break;
                case 2:
                    $l_sql = "SELECT * FROM isys_catg_raid_list WHERE isys_catg_raid_list__id = " . $this->convert_sql_id($p_f_id) . " " .
                        "AND isys_catg_raid_list__status = '" . C__RECORD_STATUS__NORMAL . "'";
                    $l_raid_res = $l_dao->retrieve($l_sql);

                    $l_row = $l_raid_res->get_row();
                    if ($l_raid_res->num_rows() > 0) {
                        $this->m_chain[$p_id][0] .= " (" . $l_row["isys_catg_raid_list__title"] . ")";
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param null $p_id
     * @param null $p_stage
     * @param null $p_f_id
     *
     * @throws isys_exception_database
     */
    public function set_chain_drive($p_id = null, $p_stage = null, $p_f_id = null)
    {

        $l_dao = new isys_cmdb_dao($this->get_database_component());

        if ($p_id == null) {
            $l_dao_driv = new isys_cmdb_dao_category_g_drive($this->get_database_component());
            $l_condition = " AND (isys_catg_drive_list__const = 'C__CATG__STORAGE' OR isys_catg_drive_list__const = 'C__CATG__RAID' OR isys_catg_drive_list__const IS NULL)";
            $l_drive_res = $l_dao_driv->get_drives(null, $_GET[C__CMDB__GET__OBJECT], $l_condition, true);

            $l_counter = 0;
            while ($l_row = $l_drive_res->get_row()) {

                $this->m_chain[$l_row["isys_catg_drive_list__id"]][] = $l_row["isys_catg_drive_list__title"];

                $this->set_chain_drive($l_row["isys_catg_drive_list__id"], 1, $l_row["isys_catg_drive_list__isys_catg_stor_list__id"]);
                $this->set_chain_drive($l_row["isys_catg_drive_list__id"], 2, $l_row["isys_catg_drive_list__isys_catg_raid_list__id"]);
                $l_counter++;
            }
        } else {

            switch ($p_stage) {
                // DEVICES
                case 1:
                    $l_sql = "SELECT * FROM isys_catg_stor_list WHERE isys_catg_stor_list__id = " . $this->convert_sql_id($p_f_id) . " " .
                        "AND isys_catg_stor_list__status = '" . C__RECORD_STATUS__NORMAL . "'";
                    $l_stor_res = $l_dao->retrieve($l_sql);
                    $l_row = $l_stor_res->get_row();

                    if ($l_stor_res->num_rows() > 0) {

                        $this->m_chain[$p_id][] .= $l_row["isys_catg_stor_list__title"];
                        $this->m_exists_dev_arr[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__id"];
                    } else {
                        $this->m_chain[$p_id][] .= "";
                    }

                    $this->set_chain_drive($p_id, 2, $l_row["isys_catg_stor_list__isys_catg_raid_list__id"]);
                    $this->set_chain_drive($p_id, 3, $l_row["isys_catg_stor_list__isys_catg_controller_list__id"]);
                    break;
                // RAIDS
                case 2:
                    $l_sql = "SELECT * FROM isys_catg_raid_list WHERE isys_catg_raid_list__id = " . $this->convert_sql_id($p_f_id) . " " .
                        "AND isys_catg_raid_list__status = '" . C__RECORD_STATUS__NORMAL . "'";
                    $l_raid_res = $l_dao->retrieve($l_sql);

                    if ($l_raid_res->num_rows() > 0) {
                        $l_row = $l_raid_res->get_row();

                        $this->m_chain[$p_id][1] .= " (" . $l_row["isys_catg_raid_list__title"] . ")";
                    }

                    break;
                // CONTROLLERS
                case 3:
                    $l_sql = "SELECT * FROM isys_catg_controller_list WHERE isys_catg_controller_list__id = " . $this->convert_sql_id($p_f_id) . " " .
                        "AND isys_catg_controller_list__status = '" . C__RECORD_STATUS__NORMAL . "'";
                    $l_con_res = $l_dao->retrieve($l_sql);
                    $l_row = $l_con_res->get_row();
                    if ($l_con_res->num_rows() > 0) {
                        $this->m_exists_con_arr[$l_row["isys_catg_controller_list__id"]] = $l_row["isys_catg_controller_list__id"];
                        $this->m_chain[$p_id][] .= $l_row["isys_catg_controller_list__title"];
                    } else {
                        $this->m_chain[$p_id][] .= "";
                    }

                default:
                    break;
            }
        }
    }

    public function attachObjects(array $p_post)
    {
        return null;
    }
}