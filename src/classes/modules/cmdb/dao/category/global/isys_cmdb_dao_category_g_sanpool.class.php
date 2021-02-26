<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 * DAO: global category for SAN pools
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_sanpool extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'sanpool';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__LDEV_SERVER';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__LDEV_SERVER;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the formatted LDEV-server data.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_capacity($p_row)
    {
        return isys_convert::retrieveFormattedMemoryByDao($p_row, $this, '__capacity');
    }

    /**
     * Callback method for the popup-browser for connected devices.
     *
     * @param   isys_request $p_request
     * @param   mixed        $p_type
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_connected_devices(isys_request $p_request, $p_type)
    {
        if (is_array($p_type) && count($p_type) == 1) {
            $p_type = $p_type[0];
        }

        $l_obj_id = $p_request->get_object_id();
        $l_return = null;
        switch ($p_type) {
            case 'devices':
                $l_dao = new isys_cmdb_dao_category_g_stor($this->get_database_component());
                $l_res = $l_dao->get_devices(null, $l_obj_id);
                $l_table = 'isys_catg_stor_list';
                break;
            case 'raids':
                $l_dao = new isys_cmdb_dao_category_g_raid($this->get_database_component());
                $l_res = $l_dao->get_raids(null, null, $l_obj_id);
                $l_table = 'isys_catg_raid_list';
                break;
        }

        while ($l_row = $l_res->get_row()) {
            $l_return[$l_row[$l_table . '__id']] = $l_row[$l_table . '__title'];
        }

        return $l_return;
    }

    /**
     * Callback function to get primary path
     *
     * @param isys_request $p_request
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_paths(isys_request $p_request)
    {
        $l_data_id = $p_request->get_category_data_id();

        $l_prim_path = $this->get_primary_path($l_data_id);
        $l_return = null;
        if (isset($l_prim_path['isys_catg_sanpool_list__primary_path'])) {
            $l_return = $l_prim_path['isys_catg_sanpool_list__primary_path'];
        }

        return $l_return;
    }

    /**
     * Method for saving a certain element.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATD__SANPOOL_TITLE'],
                $_POST['C__CATD__SANPOOL_LUN'],
                $_POST['C__CATD__SANPOOL_SEGMENT_SIZE'],
                $_POST['C__CATD__SANPOOL_CAPACITY'],
                $_POST['C__CATD__SANPOOL_UNIT'],
                $_POST['C__CATD__SANPOOL_DEVICES__HIDDEN'],
                $_POST['C__CATD__SANPOOL_DEVICES__HIDDEN2'],
                $_POST['C__CATD__SANPOOL_PATHS__HIDDEN'],
                $_POST['C__CATD__SANPOOL_PATHS__PRIM'],
                $_POST['C__CATD__SANPOOL_CLIENTS__HIDDEN'],
                $_POST['C__CATD__SANPOOL_CLIENTS__MULTIPATH'],
                $_POST['C__CATG__LDEV__TIERCLASS'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id) {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level = -1;

                return $l_id;
            }
        } else {
            $l_bRet = $this->save(
                $_GET[C__CMDB__GET__CATLEVEL],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATD__SANPOOL_TITLE'],
                $_POST['C__CATD__SANPOOL_LUN'],
                $_POST['C__CATD__SANPOOL_SEGMENT_SIZE'],
                $_POST['C__CATD__SANPOOL_CAPACITY'],
                $_POST['C__CATD__SANPOOL_UNIT'],
                $_POST['C__CATD__SANPOOL_DEVICES__HIDDEN'],
                $_POST['C__CATD__SANPOOL_DEVICES__HIDDEN2'],
                $_POST['C__CATD__SANPOOL_PATHS__HIDDEN'],
                $_POST['C__CATD__SANPOOL_PATHS__PRIM'],
                $_POST['C__CATD__SANPOOL_CLIENTS__HIDDEN'],
                $_POST['C__CATD__SANPOOL_CLIENTS__MULTIPATH'],
                $_POST['C__CATG__LDEV__TIERCLASS'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet;
    }

    /**
     * @param int    $p_catLevel
     * @param int    $p_newRecStatus
     * @param string $p_title
     * @param string $p_lun
     * @param string $p_segmentSize
     * @param mixed  $p_capacity
     * @param int    $p_unitID
     * @param mixed  $p_devices
     * @param mixed  $p_raids
     * @param mixed  $p_paths
     * @param int    $p_primPath
     * @param mixed  $p_clients
     * @param int    $p_multipathID
     * @param int    $p_tierclassID
     * @param string $p_description
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function save(
        $p_catLevel,
        $p_newRecStatus,
        $p_title,
        $p_lun,
        $p_segmentSize,
        $p_capacity,
        $p_unitID,
        $p_devices = null,
        $p_raids = null,
        $p_paths = null,
        $p_primPath = null,
        $p_clients = null,
        $p_multipathID = null,
        $p_tierclassID = null,
        $p_description = ''
    ) {
        $p_capacity = isys_convert::memory($p_capacity, $p_unitID);

        $l_update = 'UPDATE isys_catg_sanpool_list
            SET isys_catg_sanpool_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_catg_sanpool_list__lun = ' . $this->convert_sql_text($p_lun) . ',
            isys_catg_sanpool_list__segment_size = ' . $this->convert_sql_text($p_segmentSize) . ',
            isys_catg_sanpool_list__capacity = ' . $this->convert_sql_text($p_capacity) . ',
            isys_catg_sanpool_list__isys_memory_unit__id = ' . $this->convert_sql_id($p_unitID) . ',
            isys_catg_sanpool_list__isys_ldev_multipath__id = ' . $this->convert_sql_id($p_multipathID) . ',
            isys_catg_sanpool_list__isys_tierclass__id = ' . $this->convert_sql_id($p_tierclassID) . ',
            isys_catg_sanpool_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_sanpool_list__status = ' . $this->convert_sql_int($p_newRecStatus) . '
            WHERE isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_catLevel);

        if ($this->update($l_update) && $this->apply_update()) {
            $l_ret = $this->detach_devices($p_catLevel);
            if ($p_devices !== null) {
                $l_ret = $this->attach_devices($p_catLevel, $p_devices);
            }

            if ($l_ret === false) {
                // We only want to return false, if the return value really is "false" (and not null).
                return false;
            }

            $this->detach_raids($p_catLevel);
            if (!empty($p_raids)) {
                $l_ret = $this->attach_raids($p_catLevel, $p_raids);
            }

            if ($l_ret === false) {
                // We only want to return false, if the return value really is "false" (and not null).
                return false;
            }

            $this->detach_path($p_catLevel);
            if (!empty($p_primPath)) {
                $l_ret = $this->attach_path($p_catLevel, $p_paths, $p_primPath);
            }

            if ($l_ret === false) {
                // We only want to return false, if the return value really is "false" (and not null).
                return false;
            }

            $this->detach_clients($p_catLevel);
            if (!empty($p_clients)) {
                $l_ret = $this->attach_clients($p_catLevel, $p_clients);
            }

            if ($l_ret === false) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   string  $p_lun
     * @param   string  $p_segmentSize
     * @param   integer $p_capacity
     * @param   integer $p_unitID
     * @param   mixed   $p_devices
     * @param   mixed   $p_raids
     * @param   mixed   $p_paths
     * @param   integer $p_primPath
     * @param   mixed   $p_clients
     * @param   integer $p_multipathID
     * @param   string  $p_description
     *
     * @return  mixed
     * @throws  isys_exception_dao
     */
    public function create(
        $p_objID,
        $p_newRecStatus,
        $p_title,
        $p_lun,
        $p_segmentSize,
        $p_capacity,
        $p_unitID,
        $p_devices,
        $p_raids,
        $p_paths,
        $p_primPath,
        $p_clients,
        $p_multipathID,
        $p_tierclassID,
        $p_description = ''
    ) {
        $l_update = "INSERT INTO isys_catg_sanpool_list SET " . "isys_catg_sanpool_list__title = " . $this->convert_sql_text($p_title) . ", " .
            "isys_catg_sanpool_list__lun = " . $this->convert_sql_text($p_lun) . ", " . "isys_catg_sanpool_list__segment_size = " . $this->convert_sql_text($p_segmentSize) .
            ", " . "isys_catg_sanpool_list__capacity = '" . isys_convert::memory($p_capacity, $p_unitID) . "', " . "isys_catg_sanpool_list__isys_memory_unit__id = " .
            $this->convert_sql_id($p_unitID) . ", " . "isys_catg_sanpool_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_catg_sanpool_list__isys_ldev_multipath__id = " . $this->convert_sql_id($p_multipathID) . ", " . "isys_catg_sanpool_list__isys_tierclass__id = " .
            $this->convert_sql_id($p_tierclassID) . ", " . "isys_catg_sanpool_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " .
            "isys_catg_sanpool_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_update)) {
            $l_id = $this->get_last_insert_id();

            $this->apply_update();

            if (!empty($p_devices)) {
                $this->attach_devices($l_id, $p_devices);
            }

            if (!empty($p_raids)) {
                $this->attach_raids($l_id, $p_raids);
            }

            $this->attach_path($l_id, $p_paths, $p_primPath);
            $this->attach_clients($l_id, $p_clients);

            return $l_id;
        }

        return false;
    }

    /**
     * Gets all objectypes which are assigned to sanpool.
     *
     * @return  isys_component_dao_result
     */
    public function get_san_objecttypes()
    {
        $l_sql = "SELECT isys_obj__id, isys_obj_type__id FROM isys_obj_type " . "INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id " .
            "INNER JOIN isys_catg_sanpool_list ON isys_catg_sanpool_list__isys_obj__id = isys_obj__id ";

        if ($this->prepare_status_filter() != "") {
            $l_sql .= "WHERE " . $this->prepare_status_filter();
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for detaching all devices from a LDEV server.
     *
     * @param   integer $p_sanpool_list_id
     *
     * @return  boolean
     */
    public function detach_devices($p_sanpool_list_id)
    {
        $l_update = "DELETE FROM isys_catg_sanpool_list_2_isys_catg_stor_list " . "WHERE isys_catg_sanpool_list_2_isys_catg_stor_list__sanpool__id = " .
            $this->convert_sql_id($p_sanpool_list_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    public function attach_devices($p_sanpool_list_id, $p_devices)
    {
        if ($p_devices == null) {
            return null;
        }

        if (is_array($p_devices)) {
            $l_devices = $p_devices;
        } else {
            $l_devices = isys_format_json::decode($p_devices, true);
        }

        if (!is_array($l_devices) || count($l_devices) == 0) {
            return null;
        }

        $l_update = "INSERT INTO isys_catg_sanpool_list_2_isys_catg_stor_list " .
            "(isys_catg_sanpool_list_2_isys_catg_stor_list__sanpool__id, isys_catg_sanpool_list_2_isys_catg_stor_list__stor__id) VALUES ";

        $l_exe = false;

        foreach ($l_devices as $l_device_id) {
            if (is_numeric($l_device_id)) {
                $l_update .= "(" . $this->convert_sql_id($p_sanpool_list_id) . ", " . $this->convert_sql_id($l_device_id) . "),";
                $l_exe = true;
            }
        }

        if ($l_exe) {
            $l_update = substr($l_update, 0, -1);

            if ($this->update($l_update)) {
                return $this->get_last_insert_id();
            } else {
                return false;
            }
        } else {
            return null;
        }
    }

    /**
     * Method for detaching all raids from the given LDEV server.
     *
     * @param   integer $p_sanpool_list_id
     *
     * @return  boolean
     */
    public function detach_raids($p_sanpool_list_id)
    {
        $l_update = "DELETE FROM isys_catg_sanpool_list_2_isys_catg_raid_list " . "WHERE isys_catg_sanpool_list_2_isys_catg_raid_list__sanpool__id = " .
            $this->convert_sql_id($p_sanpool_list_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Method for attaching raids to the given LDEV server.
     *
     * @param   integer $p_sanpool_list_id
     * @param   mixed   $p_devices
     *
     * @return  mixed
     */
    public function attach_raids($p_sanpool_list_id, $p_devices)
    {
        if ($p_devices == null) {
            return null;
        }

        if (is_array($p_devices)) {
            $l_devices = $p_devices;
        } else {
            $l_devices = isys_format_json::decode($p_devices, true);
        }

        if (!is_array($l_devices)) {
            return null;
        }

        $l_update = "INSERT INTO isys_catg_sanpool_list_2_isys_catg_raid_list " .
            "(isys_catg_sanpool_list_2_isys_catg_raid_list__sanpool__id, isys_catg_sanpool_list_2_isys_catg_raid_list__raid__id) VALUES ";

        $l_exe = false;
        foreach ($l_devices as $l_device_id) {
            if (is_numeric($l_device_id)) {
                $l_update .= "(" . $this->convert_sql_id($p_sanpool_list_id) . ", " . $this->convert_sql_id($l_device_id) . "),";
                $l_exe = true;
            }
        }

        if ($l_exe) {
            $l_update = substr($l_update, 0, -1);

            if ($this->update($l_update)) {
                return $this->get_last_insert_id();
            } else {
                return false;
            }
        } else {
            return null;
        }
    }

    /**
     * Detach a path from the sanpool server.
     *
     * @param   integer $p_catlevel
     *
     * @return  boolean
     */
    public function detach_path($p_catlevel)
    {
        return ($this->update('DELETE FROM isys_fc_port_path WHERE isys_fc_port_path__isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_catlevel) . ';') &&
            $this->apply_update());
    }

    /**
     * Attach new path(s).
     *
     * @param   integer $p_catlevel
     * @param   mixed   $p_paths
     * @param   integer $p_primary
     *
     * @return  mixed
     */
    public function attach_path($p_catlevel, $p_paths, $p_primary)
    {
        if ($p_paths == null) {
            return null;
        }

        if (is_array($p_paths)) {
            $l_paths = $p_paths;
        } else {
            $l_paths = explode(',', $p_paths);
        }

        if (is_array($l_paths) === false) {
            return null;
        }

        $l_exe = false;
        $l_update = "INSERT INTO isys_fc_port_path (isys_fc_port_path__isys_catg_sanpool_list__id, isys_fc_port_path__isys_catg_fc_port_list__id) VALUES ";

        foreach ($l_paths as $l_path) {
            if ($l_path > 0) {
                $l_update .= "(" . $this->convert_sql_id($p_catlevel) . ", " . $this->convert_sql_id($l_path) . "),";
                $l_exe = true;
            }
        }

        if ($l_exe === false) {
            return false;
        }

        $l_update = rtrim($l_update, ',');

        if (!$this->update($l_update)) {
            return false;
        }

        $l_update = "UPDATE isys_catg_sanpool_list
			SET isys_catg_sanpool_list__primary_path = " . $this->convert_sql_id($p_primary) . "
			WHERE isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_catlevel) . ";";

        return ($this->update($l_update) && $this->apply_update());
    }

    public function detach_clients($p_catlevel)
    {
        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_dao_ldevcl = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());

        $l_data = $l_dao_ldevcl->get_clients($p_catlevel);

        $l_update = "UPDATE isys_catg_ldevclient_list SET isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id(null) . ", " .
            "isys_catg_ldevclient_list__isys_catg_relation_list__id = " . $this->convert_sql_id(null) . " " .
            "WHERE isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $p_catlevel;
        if ($this->update($l_update)) {
            if ($this->apply_update()) {
                while ($l_row = $l_data->get_row()) {
                    if (!empty($l_row["isys_catg_ldevclient_list__isys_catg_relation_list__id"])) {
                        $l_relation_dao->delete_relation($l_row["isys_catg_ldevclient_list__isys_catg_relation_list__id"]);
                    }
                }

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function attach_clients($p_catlevel, $p_clients)
    {
        if (empty($p_clients)) {
            return null;
        }

        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_dao_ldevcl = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());

        if (is_string($p_clients)) {
            if (strstr($p_clients, '[') && strstr($p_clients, ']')) {
                // We assume a JSON string.
                $l_clients = (array)isys_format_json::decode($p_clients);
            } else {
                // We assume a comma-separated list.
                if (substr($p_clients, strlen($p_clients) - 1, 1) == ",") {
                    $p_clients = substr($p_clients, 0, -1);
                }

                $l_clients = explode(",", $p_clients);
            }
        } else {
            $l_clients = $p_clients;
        }
        if (!is_array($l_clients)) {
            return null;
        }

        $l_update = "UPDATE isys_catg_ldevclient_list SET isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_catlevel) . " " . "WHERE FALSE";

        foreach ($l_clients as $l_client) {
            $l_update .= " OR isys_catg_ldevclient_list__id = " . $this->convert_sql_id($l_client);

            /* Create implicit relation */
            $l_sanpool_data = $this->get_data($p_catlevel)
                ->__to_array();
            $l_data = $l_dao_ldevcl->get_data($l_client)
                ->__to_array();

            $l_relation_dao->handle_relation(
                $l_client,
                "isys_catg_ldevclient_list",
                defined_or_default('C__RELATION_TYPE__LDEV_CLIENT'),
                $l_data["isys_catg_ldevclient_list__isys_catg_relation_list__id"],
                $l_sanpool_data["isys_catg_sanpool_list__isys_obj__id"],
                $l_data["isys_catg_ldevclient_list__isys_obj__id"]
            );
        }

        if ($this->update($l_update)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    public function get_ldevserver_by_obj_id_or_ldev_id($p_obj_id = null, $p_ldevserver_id = null, $p_table = null)
    {
        $l_sql = 'SELECT *
            FROM isys_catg_sanpool_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_sanpool_list__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE TRUE
            AND isys_catg_sanpool_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_obj_id)) {
            $l_sql .= ' AND isys_catg_sanpool_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if (!empty($p_ldevserver_id)) {
            $l_sql .= ' AND isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_ldevserver_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Retrieves the LDEV-server objects by a given object-id.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_ldevclients_by_obj($p_obj_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_ldevclient_list ' . 'WHERE isys_catg_ldevclient_list__isys_obj__id = ' . ($p_obj_id + 0) . ' ' .
            'AND isys_catg_ldevclient_list__status = ' . (C__RECORD_STATUS__NORMAL + 0) . ';';

        return $this->retrieve($l_sql);
    }

    public function get_paths($p_sanpoolID)
    {
        $l_query = "SELECT * FROM isys_fc_port_path " . "INNER JOIN isys_catg_fc_port_list ON isys_catg_fc_port_list__id = isys_fc_port_path__isys_catg_fc_port_list__id " .
            "WHERE isys_fc_port_path__isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_sanpoolID);

        return $this->retrieve($l_query);
    }

    public function get_primary_path($p_sanpoolID)
    {
        $l_sql = 'SELECT isys_catg_sanpool_list__primary_path
			FROM isys_catg_sanpool_list
			WHERE isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_sanpoolID) . ';';

        $l_res = $this->retrieve($l_sql);

        return (is_countable($l_res) && count($l_res) > 0) ? $l_res->get_row() : null;
    }

    /**
     * Method for retrieving the LDEV clients.
     *
     * @param   integer $p_sanpoolID
     * @param   boolean $p_mod
     *
     * @return  isys_component_dao_result
     */
    public function get_clients($p_sanpoolID, $p_mod = false)
    {
        $l_query = 'SELECT isys_catg_ldevclient_list__id FROM isys_catg_ldevclient_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_ldevclient_list__isys_obj__id
			WHERE isys_catg_ldevclient_list__isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_sanpoolID) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($p_mod) {
            $l_query = 'SELECT * FROM isys_catg_ldevclient_list
				INNER JOIN isys_obj ON isys_obj__id = isys_catg_ldevclient_list__isys_obj__id
				INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
				WHERE isys_catg_ldevclient_list__isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_sanpoolID) . '
				AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';
        }

        return $this->retrieve($l_query);
    }

    public function get_connected_devices($p_sanpoolID, $p_mod = false)
    {
        if ($p_mod) {
            $l_sql = 'SELECT * FROM isys_catg_sanpool_list_2_isys_catg_stor_list
				INNER JOIN isys_catg_stor_list ON  isys_catg_sanpool_list_2_isys_catg_stor_list__stor__id = isys_catg_stor_list__id
				WHERE isys_catg_sanpool_list_2_isys_catg_stor_list__sanpool__id = ' . $this->convert_sql_id($p_sanpoolID);

            return $this->retrieve($l_sql);
        }

        $l_query = 'SELECT isys_catg_sanpool_list_2_isys_catg_stor_list__stor__id FROM isys_catg_sanpool_list_2_isys_catg_stor_list
			WHERE isys_catg_sanpool_list_2_isys_catg_stor_list__sanpool__id = ' . $this->convert_sql_id($p_sanpoolID);

        return $this->retrieve($l_query);
    }

    public function get_connected_raids($p_sanpoolID, $p_mod = false)
    {
        $l_query = "SELECT isys_catg_sanpool_list_2_isys_catg_raid_list__raid__id FROM isys_catg_sanpool_list_2_isys_catg_raid_list " .
            "WHERE isys_catg_sanpool_list_2_isys_catg_raid_list__sanpool__id = " . $this->convert_sql_id($p_sanpoolID);

        if ($p_mod) {
            $l_query = "SELECT * FROM isys_catg_sanpool_list_2_isys_catg_raid_list " .
                "INNER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_sanpool_list_2_isys_catg_raid_list__raid__id " .
                "WHERE isys_catg_sanpool_list_2_isys_catg_raid_list__sanpool__id = " . $this->convert_sql_id($p_sanpoolID);
        }

        return $this->retrieve($l_query);
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function get_device_name($id)
    {
        return $this
            ->retrieve('SELECT isys_catg_sanpool_list__title FROM isys_catg_sanpool_list WHERE isys_catg_sanpool_list__id = ' . $this->convert_sql_id($id) . ';')
            ->get_row_value('isys_catg_sanpool_list__title');
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return array|string
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                $l_objects = $this->get_ldevserver_by_obj_id_or_ldev_id($_GET[C__CMDB__GET__OBJECT]);

                while ($l_row = $l_objects->get_row()) {
                    $l_return[] = [
                        '__checkbox__'                          => $l_row["isys_catg_sanpool_list__id"],
                        $language->get('LC__CATG__ODEP_OBJ')    => $l_row["isys_catg_sanpool_list__title"],
                        $language->get('LC__CATD__SANPOOL_LUN') => $l_row['isys_catg_sanpool_list__lun'],
                        $language->get('LC__CMDB__OBJTYPE')     => $language->get($l_row["isys_obj_type__title"])
                    ];
                }

                return json_encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                // Save a bit memory: Only select needed fields!
                $l_sql = "SELECT *
                    FROM isys_catg_sanpool_list AS san
                    LEFT JOIN isys_obj AS obj ON san.isys_catg_sanpool_list__isys_obj__id = obj.isys_obj__id 
                    WHERE san.isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_parameters['preselection']) . "
                    LIMIT 1;";

                $l_row = $this->retrieve($l_sql)->get_row();

                // @see  ID-6220  Also return a 'first' selection.
                $l_preselection['first'][] = $l_row['isys_obj__id'];

                $l_return['second'] = [
                    $l_row['isys_catg_sanpool_list__id'],
                    $l_row['isys_catg_sanpool_list__title'],
                    $l_row['isys_catg_sanpool_list__lun'],
                    $l_row['?'],
                ];

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $daoLdevClient = isys_cmdb_dao_category_g_ldevclient::instance($this->m_db);

                        $categoryRow = $this->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_sanpool_list__id'],
                            $categoryRow['isys_obj__title'],
                            $language->get($daoLdevClient->get_objtype_name_by_id_as_string($categoryRow['isys_obj__isys_obj_type__id'])),
                            $categoryRow['isys_catg_sanpool_list__title'],
                            $categoryRow['isys_catg_sanpool_list__lun']
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE'),
                        $language->get('LC__CATG__ODEP_OBJ'),
                        $language->get('LC__CATD__SANPOOL_LUN')
                    ],
                    'data'   => $preselection
                ];
        }

        return null;
    }

    /**
     * @param $p_ldevserverid
     *
     * @return string
     */
    public function format_selection($p_ldevserverid, $plain = true)
    {
        // We need a DAO for the object name.
        $l_dao_cmdb = new isys_cmdb_dao($this->m_db);
        $l_dao_ldevserver = new isys_cmdb_dao_category_g_sanpool($this->m_db);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_ldev_res = $l_dao_ldevserver->get_ldevserver_by_obj_id_or_ldev_id(null, $p_ldevserverid);
        $l_ldev_row = $l_ldev_res->get_row();

        $p_object_type = $l_dao_cmdb->get_objTypeID($l_ldev_row["isys_catg_sanpool_list__isys_obj__id"]);

        if (!empty($p_ldevserverid)) {
            if ($plain) {
                return isys_application::instance()->container->get('language')
                        ->get($l_dao_cmdb->get_objtype_name_by_id_as_string($p_object_type)) . " >> " .
                    $l_dao_cmdb->get_obj_name_by_id_as_string($l_ldev_row["isys_catg_sanpool_list__isys_obj__id"]) . " >> " . $l_ldev_row["isys_catg_sanpool_list__title"] .
                    " (LUN: " . $l_ldev_row["isys_catg_sanpool_list__lun"] . ") ";
            } else {
                return $l_quick_info->get_link(
                    $p_ldevserverid,
                    isys_application::instance()->container->get('language')
                        ->get($l_dao_cmdb->get_objtype_name_by_id_as_string($p_object_type)) . " >> " .
                    $l_dao_cmdb->get_obj_name_by_id_as_string($l_ldev_row["isys_catg_sanpool_list__isys_obj__id"]) . " >> " . $l_ldev_row["isys_catg_sanpool_list__title"] .
                    " (LUN: " . $l_ldev_row["isys_catg_sanpool_list__lun"] . ")",
                    "index.php?viewMode=1100&objTypeID=" . $l_dao_cmdb->get_objTypeID($l_ldev_row["isys_catg_sanpool_list__isys_obj__id"]) . "&objID=" .
                    $l_ldev_row["isys_catg_sanpool_list__isys_obj__id"] . "&tvMode=1006&editMode=0&mNavID=2&cateID=" . $p_ldevserverid . "&catgID=" .
                    $l_dao_ldevserver->get_category_id()
                );
            }
        }

        return isys_application::instance()->container->get('language')
            ->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
    }

    /**
     * Format selection for the storage-device-browser.
     *
     * @param string $clients
     *
     * @return string
     * @throws Exception
     */
    public function format_selection2($clients)
    {
        $return = [];

        $ldevDao = isys_cmdb_dao_category_g_ldevclient::instance($this->m_db);
        $editMode = isys_glob_is_edit_mode();

        $clientResult = $ldevDao->get_client_info(explode(",", $clients));
        $quickInfo = new isys_ajax_handler_quick_info();

        if ($clientResult->num_rows() == 0) {
            return isys_application::instance()->container->get('language')
                ->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
        }

        while ($row = $clientResult->get_row()) {
            if ($editMode) {
                $return[] = $ldevDao->get_obj_name_by_id_as_string($row["isys_obj__id"]) . " >> " . $row["isys_catg_ldevclient_list__title"];
            } else {
                $return[] = $quickInfo->get_link(
                    $row["isys_catg_ldevclient_list__id"],
                    $ldevDao->get_obj_name_by_id_as_string($row["isys_obj__id"]) . " >> " . $row["isys_catg_ldevclient_list__title"],
                    isys_helper_link::create_url([
                        C__CMDB__GET__VIEWMODE           => C__CMDB__VIEW__CATEGORY,
                        C__CMDB__GET__OBJECTTYPE         => $ldevDao->get_objTypeID($row["isys_obj__id"]),
                        C__CMDB__GET__OBJECT             => $row["isys_obj__id"],
                        C__CMDB__GET__TREEMODE           => C__CMDB__VIEW__TREE_OBJECT,
                        C__CMDB__GET__EDITMODE           => 0,
                        C__GET__MAIN_MENU__NAVIGATION_ID => 2,
                        C__CMDB__GET__CATLEVEL           => $row["isys_catg_ldevclient_list__id"],
                        C__CMDB__GET__CATG               => $ldevDao->get_category_id()
                    ])
                );
            }
        }

        $glue = $editMode ? ', ' : ',<br />' . (new isys_smarty_plugin_f())->getInfoIcon([]);

        return implode($glue, $return);
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param   integer $p_context
     * @param   array   $p_parameters
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser2($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                $l_objects = $this->get_ldevclients_by_obj($_GET[C__CMDB__GET__OBJECT]);

                while ($l_row = $l_objects->get_row()) {
                    $l_return[] = [
                        '__checkbox__'                  => $l_row['isys_catg_ldevclient_list__id'],
                        $language->get('LC__CATG__ODEP_OBJ') => $l_row["isys_catg_ldevclient_list__title"],
                    ];
                }

                return isys_format_json::encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                if (!empty($p_parameters['preselection'])) {
                    if (isys_format_json::is_json_array($p_parameters['preselection'])) {
                        $l_ids = isys_format_json::decode($p_parameters['preselection']);
                    } else {
                        $l_ids = explode(',', $p_parameters['preselection']);
                    }

                    // Save a bit memory: Only select needed fields!
                    $l_sql = "SELECT isys_obj__id, isys_catg_ldevclient_list__id, isys_catg_ldevclient_list__title, isys_obj__title
						FROM isys_catg_relation_list AS rl
						LEFT JOIN isys_catg_ldevclient_list AS ldev ON ldev.isys_catg_ldevclient_list__isys_catg_relation_list__id = rl.isys_catg_relation_list__id
						LEFT JOIN isys_obj AS obj ON rl.isys_catg_relation_list__isys_obj__id__slave = obj.isys_obj__id
						WHERE isys_catg_ldevclient_list__id " . $this->prepare_in_condition($l_ids);

                    $l_dao_result = new isys_component_dao($this->m_db);
                    $l_res = $l_dao_result->retrieve($l_sql);

                    while ($l_row = $l_res->get_row()) {
                        // @see  ID-6220  Also return a 'first' selection.
                        $l_preselection['first'][] = $l_row['isys_obj__id'];

                        $l_return['second'][] = [
                            $l_row['isys_catg_ldevclient_list__id'],
                            $l_row['isys_obj__title'] . ' >> ' . $l_row['isys_catg_ldevclient_list__title'],
                            $language->get('LC__CMDB__OBJTYPE__RELATION'),
                            $language->get('LC__CMDB__OBJTYPE__RELATION'),
                        ];
                    }
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $daoLdevClient = isys_cmdb_dao_category_g_ldevclient::instance($this->m_db);

                        $categoryRow = $daoLdevClient
                            ->get_data($dataId)
                            ->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_port_list__id'],
                            $categoryRow['isys_obj__title'],
                            $language->get($daoLdevClient->get_objtype_name_by_id_as_string($categoryRow['isys_obj__isys_obj_type__title'])),
                            $categoryRow['isys_catg_ldevclient_list__title']
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE'),
                        $language->get('LC__CATG__ODEP_OBJ')
                    ],
                    'data'   => $preselection
                ];
        }

        return [];
    }

    /**
     * Return all object-types, which have related ldev-client-elements.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  array
     */
    public function object_types_for_ldev_clients()
    {
        $l_return = [];

        $l_result = $this->retrieve('SELECT DISTINCT(isys_obj_type__const) FROM isys_obj_type
			INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
			INNER JOIN isys_catg_ldevclient_list ON isys_catg_ldevclient_list__isys_obj__id = isys_obj__id;');

        while ($l_row = $l_result->get_row()) {
            $l_return[] = $l_row['isys_obj_type__const'];
        }

        return $l_return;
    }

    /**
     * Return all object-types, which have related sanpool-elements.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  array
     */
    public function object_types()
    {
        $l_return = [];

        $l_sql = "SELECT isys_obj_type__const FROM isys_obj_type
			INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
			INNER JOIN isys_catg_sanpool_list ON isys_catg_sanpool_list__isys_obj__id = isys_obj__id";
        $l_result = $this->retrieve($l_sql);

        while ($l_row = $l_result->get_row()) {
            $l_return[] = $l_row['isys_obj_type__const'];
        }

        return $l_return;
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_capacity' => new DynamicProperty(
                'LC__CMDB__CATG__LDEV_SERVER__CAPACITY',
                'isys_catg_sanpool_list__id',
                'isys_catg_sanpool_list',
                [
                    $this,
                    'dynamic_property_callback_capacity'
                ]
            ),
        ];
    }

    /**
     * Method for counting the category rows.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id > 0) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = 'SELECT count(isys_obj__id) AS count FROM isys_catg_sanpool_list
			LEFT JOIN isys_obj ON isys_catg_sanpool_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_sanpool_list__isys_memory_unit__id
			WHERE TRUE';

        if ($l_obj_id > 0) {
            $l_sql .= ' AND isys_catg_sanpool_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        }

        $l_data = $this->retrieve($l_sql . ';')
            ->get_row();

        return $l_data["count"];
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catd_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   string  $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @see     isys_cmdb_dao_category::get_data()
     */
    public function get_data($p_catd_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_sanpool_list " . "INNER JOIN isys_obj ON isys_catg_sanpool_list__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_sanpool_list__isys_memory_unit__id " .
            "LEFT JOIN isys_ldev_multipath ON isys_ldev_multipath__id = isys_catg_sanpool_list__isys_ldev_multipath__id " .
            "LEFT JOIN isys_tierclass ON isys_tierclass__id = isys_catg_sanpool_list__isys_tierclass__id " . "WHERE TRUE " . $p_condition . " " .
            $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catd_list_id !== null) {
            $l_sql .= " AND isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_catd_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_sanpool_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws Exception
     */
    protected function properties()
    {
        return [
            'title'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sanpool_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_sanpool_list__title FROM isys_catg_sanpool_list',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATD__SANPOOL_TITLE'
                ]
            ]),
            'lun'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LUN',
                    C__PROPERTY__INFO__DESCRIPTION => 'LUN'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sanpool_list__lun',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_sanpool_list__lun FROM isys_catg_sanpool_list',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATD__SANPOOL_LUN'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'segment_size'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD__SANPOOL_SEGMENT_SIZE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Segment size (kB)'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sanpool_list__segment_size',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_sanpool_list__segment_size FROM isys_catg_sanpool_list',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATD__SANPOOL_SEGMENT_SIZE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'unit'              => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB_CATG__MEMORY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_sanpool_list__isys_memory_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title
                            FROM isys_catg_sanpool_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_sanpool_list__isys_memory_unit__id',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_sanpool_list', 'LEFT', 'isys_catg_sanpool_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_sanpool_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__SANPOOL_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_memory_unit',
                        'p_strClass' => 'input-mini',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'capacity'          => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CAPACITY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sanpool_list__capacity',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_sanpool_list__capacity, \',\', isys_memory_unit__title, \'}\')
                            FROM isys_catg_sanpool_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_sanpool_list__isys_memory_unit__id',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_sanpool_list', 'LEFT', 'isys_catg_sanpool_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_sanpool_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__SANPOOL_CAPACITY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['memory']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'unit'
                ]
            ]),
            'connected_devices' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATD__SANPOOL_DEVICES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Attached devices'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sanpool_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_ldevclient_list',
                            'isys_catg_ldevclient_list__isys_catg_sanpool_list__id'
                        ],
                        C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                            '
                            SELECT GROUP_CONCAT(CONCAT(raidstor.title, \' {\', isys_catg_sanpool_list__isys_obj__id, \'}\') SEPARATOR \'<br />\')
                        	FROM isys_catg_sanpool_list
                        	LEFT JOIN
                        	(
	                        	(
	                        		SELECT isys_catg_raid_list__title AS title, isys_catg_sanpool_list_2_isys_catg_raid_list__sanpool__id AS id FROM isys_catg_sanpool_list_2_isys_catg_raid_list
	                        		INNER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_sanpool_list_2_isys_catg_raid_list__raid__id
                        		)
                        		UNION
                        		(
	                        		SELECT isys_catg_stor_list__title AS title, isys_catg_sanpool_list_2_isys_catg_stor_list__sanpool__id AS id FROM isys_catg_sanpool_list_2_isys_catg_stor_list
	                        		INNER JOIN isys_catg_stor_list ON isys_catg_stor_list__id = isys_catg_sanpool_list_2_isys_catg_stor_list__stor__id
                        		)
                        	) AS raidstor ON raidstor.id = isys_catg_sanpool_list__id',
                            'isys_catg_sanpool_list',
                            'isys_catg_sanpool_list__id',
                            'isys_catg_sanpool_list__isys_obj__id',
                            '',
                            '',
                            idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([])
                        )
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATD__SANPOOL_DEVICES',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection'    => true,
                            'p_strPopupType'    => 'browser_sanpool',
                            'p_selectedDevices' => new isys_callback([
                                'isys_cmdb_dao_category_g_sanpool',
                                'callback_property_connected_devices'
                            ], ['devices']),
                            'p_selectedRaids'   => new isys_callback([
                                'isys_cmdb_dao_category_g_sanpool',
                                'callback_property_connected_devices'
                            ], ['raids'])
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'ldev_connected_devices'
                        ]
                    ]
                ]),
            // @todo fc-port browser is used data retrieval is in class isys_popup_browser_fc_port should be separated
            'paths'             => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__PATHS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Paths'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sanpool_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_sanpool_list__primary_path = isys_catg_fc_port_list__id THEN
                              CONCAT(isys_catg_fc_port_list__title, \' (LC__UNIVERSAL__PRIMARY)\')
                              ELSE isys_catg_fc_port_list__title END)
                            FROM isys_catg_sanpool_list
                            INNER JOIN isys_fc_port_path ON isys_fc_port_path__isys_catg_sanpool_list__id = isys_catg_sanpool_list__id
                            INNER JOIN isys_catg_fc_port_list ON isys_catg_fc_port_list__id = isys_fc_port_path__isys_catg_fc_port_list__id',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__SANPOOL_PATHS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPrim'      => new isys_callback([
                            'isys_cmdb_dao_category_g_sanpool',
                            'callback_property_paths'
                        ]),
                        'p_strValue' => new isys_callback([
                            'isys_cmdb_dao_category_g_sanpool',
                            'callback_property_path_preselection'
                        ]),
                        'multiselection' => true,
                        'p_strPopupType' => 'browser_fc_port',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'ldev_paths',
                    ]
                ]
            ]),
            'primary_path'        => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LDEVCLIENT__PRIMARY_PATH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary path'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sanpool_list__primary_path',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_fc_port_list__title
                              FROM isys_catg_sanpool_list
                            INNER JOIN isys_catg_fc_port_list ON isys_catg_fc_port_list__id = isys_catg_sanpool_list__primary_path',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_sanpool_list',
                            'LEFT',
                            'isys_catg_sanpool_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_fc_port_list',
                            'LEFT',
                            'isys_catg_sanpool_list__primary_path',
                            'isys_catg_fc_port_list__id'
                        ),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATD__SANPOOL_PATHS__PRIM'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__REPORT  => true,
                    C__PROPERTY__PROVIDES__LIST    => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'multipath'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LDEV_MULTI_PATH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Multipath technology'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_sanpool_list__isys_ldev_multipath__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_ldev_multipath',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_ldev_multipath',
                        'isys_ldev_multipath__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_ldev_multipath__title
                            FROM isys_catg_sanpool_list
                            INNER JOIN isys_ldev_multipath ON isys_ldev_multipath__id = isys_catg_sanpool_list__isys_ldev_multipath__id',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_sanpool_list', 'LEFT', 'isys_catg_sanpool_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_ldev_multipath',
                            'LEFT',
                            'isys_catg_sanpool_list__isys_ldev_multipath__id',
                            'isys_ldev_multipath__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATD__SANPOOL_CLIENTS__MULTIPATH',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_ldev_multipath',
                        'p_strClass' => 'input input-small'
                    ]
                ]
            ]),
            'tierclass'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__LDEV__TIERCLASS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Tier class'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_sanpool_list__isys_tierclass__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_tierclass',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_tierclass',
                        'isys_tierclass__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_tierclass__title
                            FROM isys_catg_sanpool_list
                            INNER JOIN isys_tierclass ON isys_tierclass__id = isys_catg_sanpool_list__isys_tierclass__id',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_sanpool_list', 'LEFT', 'isys_catg_sanpool_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_tierclass',
                            'LEFT',
                            'isys_catg_sanpool_list__isys_tierclass__id',
                            'isys_tierclass__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LDEV__TIERCLASS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_tierclass',
                        'p_strClass' => 'input input-small'
                    ]
                ]
            ]),
            'ldev_clients'      => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LDEV_CLIENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Logical devices (Client)'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_sanpool_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_ldevclient_list',
                        'isys_catg_ldevclient_list__isys_catg_sanpool_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' > \', isys_catg_ldevclient_list__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_sanpool_list
                            INNER JOIN isys_catg_ldevclient_list ON isys_catg_ldevclient_list__isys_catg_sanpool_list__id = isys_catg_sanpool_list__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_ldevclient_list__isys_obj__id',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_sanpool_list', 'LEFT', 'isys_catg_sanpool_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ldevclient_list',
                            'LEFT',
                            'isys_catg_sanpool_list__id',
                            'isys_catg_ldevclient_list__isys_catg_sanpool_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_ldevclient_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__SANPOOL_CLIENTS',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection'   => true,
                        // @todo Property Callback for multiedit (in future).
                        'secondSelection'  => true,
                        'catFilter'        => 'C__CATG__SANPOOL;C__CATG__LDEV_CLIENT;C__CATG__LDEV_SERVER',
                        'secondList'       => 'isys_cmdb_dao_category_g_sanpool::object_browser2',
                        'secondListFormat' => 'isys_cmdb_dao_category_g_sanpool::format_selection2'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'ldev_connected_clients'
                    ]
                ]
            ]),
            'description'       => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sanpool_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_sanpool_list__description FROM isys_catg_sanpool_list',
                        'isys_catg_sanpool_list',
                        'isys_catg_sanpool_list__id',
                        'isys_catg_sanpool_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sanpool_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__LDEV_SERVER', 'C__CATG__LDEV_SERVER')
                ]
            ])
        ];
    }

    /**
     * Callback: Get selected fc ports based on categoryDataId
     *
     * @param isys_request $request
     *
     * @return string
     */
    public function callback_property_path_preselection(isys_request $request)
    {
        // Collect and assign needed resources
        $categoryDataId = $request->get_category_data_id();
        $selectedPorts = [];

        // Check whether categoryDataId was provided
        if (!empty($categoryDataId)) {
            // Get paths by categoryDataId
            $dbRes = $this->get_paths($categoryDataId);

            // Collect them for post processing
            if ($dbRes->num_rows()) {
                while ($selectedPort = $dbRes->get_row()) {
                    $selectedPorts[] = $selectedPort['isys_fc_port_path__isys_catg_fc_port_list__id'];
                }
            }
        }

        return implode(',', $selectedPorts);
    }

    /**
     * Method for syncing.
     *
     * @see     isys_cmdb_dao_category::sync()
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_arr_ldev_clients = $l_arr_raids = $l_arr_devices = [];

            $this->m_sync_catg_data = $p_category_data;
            $l_connected_devices = $this->get_property('connected_devices');
            // Assigned devices and raids
            if (is_array($l_connected_devices)) {
                foreach ($l_connected_devices as $l_key => $l_value) {
                    switch ($l_value['type']) {
                        case 'C__CATG__RAID':
                            $l_arr_raids[] = $l_value[C__DATA__VALUE];
                            break;
                        case 'C__CATG__STORAGE_DEVICE':
                        case 'C__CMDB__SUBCAT__STORAGE__DEVICE': // @todo  Remove in i-doit 1.12
                            $l_arr_devices[] = $l_value[C__DATA__VALUE];
                            break;
                    }
                }
            }

            $l_ldev_clients = $this->get_property('ldev_clients');

            // Assigned ldevclients
            if (is_array($l_ldev_clients)) {
                foreach ($l_ldev_clients as $l_key => $l_value) {
                    $l_arr_ldev_clients[] = $l_value['ref_id'];
                }
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('lun'),
                        $this->get_property('segment_size'),
                        $this->get_property('capacity'),
                        $this->get_property('unit'),
                        $l_arr_devices,
                        $l_arr_raids,
                        $this->get_property('paths'),
                        $this->get_property('primary_path'),
                        $l_arr_ldev_clients,
                        $this->get_property('multipath'),
                        $this->get_property('tierclass'),
                        $this->get_property('description')
                    );

                    if ($p_category_data['data_id']) {
                        $l_indicator = true;
                    }
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('lun'),
                        $this->get_property('segment_size'),
                        $this->get_property('capacity'),
                        $this->get_property('unit'),
                        $l_arr_devices,
                        $l_arr_raids,
                        $this->get_property('paths'),
                        $this->get_property('primary_path'),
                        $l_arr_ldev_clients,
                        $this->get_property('multipath'),
                        $this->get_property('tierclass'),
                        $this->get_property('description')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
