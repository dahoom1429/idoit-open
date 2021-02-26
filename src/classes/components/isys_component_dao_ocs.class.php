<?php

/**
 * i-doit
 *
 * DAO for OCS
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Dennis Blümer <dbluemer@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_ocs extends isys_component_dao
{
    /**
     * Version switch
     *
     * @var bool
     */
    private $isOld = false;

    /**
     * isys_component_dao_ocs constructor.
     *
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        try {
            $this->isOld = $this->retrieve('SHOW TABLES LIKE ' . $this->convert_sql_text('softwares'))->count() > 0 ? true : false;
        } catch (Exception $e) {
        }
    }

    /**
     * Gets all OCS databases
     *
     * @return isys_component_dao_result
     */
    public function getOCSDBs()
    {
        return $this->retrieve("SELECT * FROM isys_ocs_db");
    }

    /**
     * Gets OCS database by ID
     *
     * @param null $p_id
     *
     * @return array|null
     */
    public function getOCSDB($p_id = null)
    {
        $l_query = "SELECT * FROM isys_ocs_db";

        if ($p_id != null) {
            $l_query .= " WHERE isys_ocs_db__id = " . $this->convert_sql_id($p_id);
        }

        $l_res = $this->retrieve($l_query);

        if ($l_res->num_rows() == 0) {
            return null;
        }

        return $l_res->get_row();
    }

    /**
     * Get OCS database by schema
     *
     * @param $p_schema
     *
     * @return bool|isys_component_dao_result
     */
    public function get_ocs_db_by_schema($p_schema)
    {
        if (empty($p_schema)) {
            return false;
        }

        $l_query = 'SELECT * FROM isys_ocs_db WHERE isys_ocs_db__schema LIKE ' . $this->convert_sql_text($p_schema);

        return $this->retrieve($l_query);
    }

    /**
     * Get OCS database id by schema.
     *
     * @param   string $p_schema
     *
     * @return  bool
     */
    public function get_ocs_db_id_by_schema($p_schema)
    {
        if (empty($p_schema)) {
            return false;
        }

        $l_res = $this->retrieve('SELECT * FROM isys_ocs_db WHERE isys_ocs_db__schema LIKE ' . $this->convert_sql_text($p_schema) . ';');

        if ($l_res) {
            return $l_res->get_row_value('isys_ocs_db__id');
        }

        return false;
    }

    /**
     * Update or create a new ocs database
     *
     * @param $p_id
     *
     * @return bool|int
     */
    public function saveOCSDB($p_id)
    {
        if ($p_id != null) {
            $l_update = "UPDATE isys_ocs_db SET ";
        } else {
            $l_update = "INSERT INTO isys_ocs_db SET ";
        }

        $l_password = isys_helper_crypt::encrypt($_POST["C__MODULE__IMPORT__OCS_PASS"]);

        if ($_POST['C__MODULE__IMPORT__OCS_PASS__action'] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
            $l_password = null;
        }

        $l_update .= "isys_ocs_db__host = " . $this->convert_sql_text($_POST["C__MODULE__IMPORT__OCS_HOST"]) . ",
            isys_ocs_db__port = " . $this->convert_sql_text($_POST["C__MODULE__IMPORT__OCS_PORT"]) . ",
            isys_ocs_db__schema = " . $this->convert_sql_text($_POST["C__MODULE__IMPORT__OCS_SCHEMA"]) . ",
            isys_ocs_db__user = " . $this->convert_sql_text($_POST["C__MODULE__IMPORT__OCS_USER"]);

        if ($l_password !== null) {
            $l_update .= ', isys_ocs_db__pass = ' . $this->convert_sql_text($l_password);
        }

        if ($p_id != null) {
            $l_update .= " WHERE isys_ocs_db__id = " . $this->convert_sql_id($p_id);
        }

        if ($this->update($l_update . ';') && $this->apply_update()) {
            if ($p_id != null) {
                return $p_id;
            } else {
                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * Remove existing ocs database from the db
     *
     * @param $p_id
     *
     * @return bool
     */
    public function delete_ocsdb($p_id)
    {
        if ($p_id > 0) {
            $l_update = "DELETE FROM isys_ocs_db WHERE isys_ocs_db__id = " . $this->convert_sql_id($p_id);

            $this->begin_update();

            try {
                $this->update($l_update);
                $this->apply_update();

                return true;
            } catch (Exception $e) {
                $this->cancel_update();
            }
        }

        return false;
    }

    /**
     * Get all Device IDs by hostname
     *
     * @param $p_arHostnames
     *
     * @return isys_component_dao_result
     */
    public function getHardwareIDs($p_arHostnames)
    {
        $l_query = "SELECT ID FROM hardware WHERE FALSE ";
        foreach ($p_arHostnames as $l_hostname) {
            $l_query .= " OR NAME = " . $this->convert_sql_text($l_hostname);
        }

        return $this->retrieve($l_query);
    }

    /**
     * Get all devices
     *
     * @return isys_component_dao_result
     */
    public function getHardware()
    {
        $l_query = "SELECT * FROM hardware LEFT JOIN accountinfo ON hardware.ID = accountinfo.HARDWARE_ID ";
        if ($this->does_snmp_exist()) {
            $l_query .= ' WHERE (SELECT ID FROM snmp WHERE snmp.NAME = CONCAT(hardware.name, \'.\', hardware.workgroup) LIMIT 1) IS NULL ';
        }
        $l_query .= 'ORDER BY ID ASC;';

        return $this->retrieve($l_query);
    }

    /**
     * Get Tag
     *
     * @param $p_hardwareID
     *
     * @return mixed
     */
    public function getTag($p_hardwareID)
    {
        $l_res = $this->retrieve("SELECT TAG FROM accountinfo WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
        $l_row = $l_res->get_row();

        return $l_row["TAG"];
    }

    /**
     * Get device info by hardware ID
     *
     * @param $p_hardwareID
     *
     * @return array
     */
    public function getHardwareItem($p_hardwareID)
    {
        $l_res = $this->retrieve("SELECT hardware.*, accountinfo.*, bios.* FROM hardware
			LEFT JOIN accountinfo ON accountinfo.HARDWARE_ID = hardware.ID
			LEFT JOIN bios ON bios.HARDWARE_ID = hardware.ID
			LEFT JOIN networks ON networks.HARDWARE_ID = hardware.ID
			WHERE hardware.ID = " . $this->convert_sql_id($p_hardwareID) . " AND accountinfo.HARDWARE_ID IS NOT NULL
			GROUP BY hardware.ID");

        return $l_res->get_row();
    }

    /**
     * Get Memory by hardware ID
     *
     * @param      $p_hardwareID
     * @param bool $p_snmp
     *
     * @return isys_component_dao_result
     */
    public function getMemory($p_hardwareID, $p_snmp = false)
    {
        if ($p_snmp) {
            return $this->retrieve("SELECT * FROM snmp_memories WHERE SNMP_ID = " . $this->convert_sql_id($p_hardwareID));
        } else {
            return $this->retrieve("SELECT * FROM memories WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
        }
    }

    /**
     * Get graphic adapter by hardware ID
     *
     * @param $p_hardwareID
     *
     * @return isys_component_dao_result
     */
    public function getGraphicsAdapter($p_hardwareID)
    {
        return $this->retrieve("SELECT * FROM videos WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
    }

    /**
     * Get sound card info by hardware ID
     *
     * @param $p_hardwareID
     *
     * @return isys_component_dao_result
     */
    public function getSoundAdapter($p_hardwareID)
    {
        return $this->retrieve("SELECT * FROM sounds WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
    }

    /**
     * Get OS info by hardware ID
     *
     * @param $p_hardwareID
     *
     * @return isys_component_dao_result
     */
    public function getBios($p_hardwareID, $p_snmp = false)
    {
        if ($p_snmp) {
            $l_sql = 'SELECT TRIM(SERIALNUMBER) AS SERIALNUMBER, NULL AS MANUFACTURER, NULL AS REFERENCE, NULL AS FIRMVERSION, snmp_printers.NAME AS DESCRIPTION, NULL AS TYPE, NULL AS TITLE, soft1.NAME AS FIRMVERSION_IOS
				FROM snmp_printers
				LEFT JOIN snmp_softwares AS soft1 ON soft1.SNMP_ID = snmp_printers.SNMP_ID AND soft1.COMMENTS = \'IOS\'
				WHERE snmp_printers.SNMP_ID = ' . $this->convert_sql_id($p_hardwareID) . '
			 	UNION
			 	SELECT SERIALNUMBER, MANUFACTURER, REFERENCE, FIRMVERSION, DESCRIPTION, snmp_switchinfos.TYPE AS TYPE, CONCAT(REFERENCE, \' - \', SERIALNUMBER, \' - \', snmp_switchs.TYPE) AS TITLE, soft2.NAME AS FIRMVERSION_IOS
			 	FROM snmp_switchs
			 	INNER JOIN snmp_switchinfos ON snmp_switchinfos.SNMP_ID = snmp_switchs.SNMP_ID
			 	LEFT JOIN snmp_softwares AS soft2 ON soft2.SNMP_ID = snmp_switchs.SNMP_ID AND soft2.COMMENTS = \'IOS\'
			 	WHERE snmp_switchs.SNMP_ID = ' . $this->convert_sql_id($p_hardwareID) . '
			 	UNION
			 	SELECT SERIALNUMBER, NULL AS MANUFACTURER, NULL AS REFERENCE, NULL AS FIRMVERSION, SYSTEM AS DESCRIPTION, NULL AS TYPE, NULL AS TITLE, soft3.NAME AS FIRMVERSION_IOS
			 	FROM snmp_blades
			 	 LEFT JOIN snmp_softwares AS soft3 ON soft3.SNMP_ID = snmp_blades.SNMP_ID AND soft3.COMMENTS = \'IOS\'
			 	WHERE snmp_blades.SNMP_ID = ' . $this->convert_sql_id($p_hardwareID);

            return $this->retrieve($l_sql);
        } else {
            return $this->retrieve("SELECT *, TRIM(SSN) AS SSN FROM bios WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
        }
    }

    /**
     * Get application assignments by hardware ID
     *
     * @param      $p_hardwareID
     *
     * @param bool $p_snmp
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getSoftware($p_hardwareID, $p_snmp = false)
    {
        $l_query = "SELECT software.*, sn.NAME, vs.VERSION, ps.PUBLISHER FROM hardware 
             INNER JOIN software ON software.HARDWARE_ID = hardware.ID AND software.NAME_ID != ''
             INNER JOIN software_name AS sn ON sn.ID = software.NAME_ID 
             INNER JOIN software_version AS vs ON vs.ID = software.VERSION_ID 
             INNER JOIN software_publisher AS ps ON ps.ID = software.PUBLISHER_ID 
             WHERE hardware.ID = " . $this->convert_sql_id($p_hardwareID) .
            " AND hardware.OSNAME != sn.NAME AND sn.NAME NOT IN (SELECT EXTRACTED FROM dico_ignored)";

        if ($p_snmp) {
            $l_query = "SELECT * FROM snmp_softwares WHERE SNMP_ID = " . $this->convert_sql_id($p_hardwareID);
        } elseif ($this->isOld) {
            $l_query = "SELECT softwares.* FROM hardware 
                INNER JOIN softwares ON softwares.HARDWARE_ID = hardware.ID AND softwares.NAME != '' 
                WHERE hardware.ID = " . $this->convert_sql_id($p_hardwareID) .
                " AND hardware.OSNAME != softwares.NAME AND softwares.NAME NOT IN (SELECT EXTRACTED FROM dico_ignored)";
        }

        return $this->retrieve($l_query);
    }

    /**
     * Get all applications only from hardware
     *
     * @return isys_component_dao_result
     */
    public function getAllSoftware()
    {
        $l_query = "SELECT DISTINCT(sn.NAME) FROM software " .
            " INNER JOIN software_name AS sn ON sn.ID = software.NAME_ID " .
            " LEFT JOIN hardware ON hardware.OSNAME = sn.NAME " .
            " WHERE hardware.OSNAME IS NULL";

        if ($this->isOld) {
            $l_query = "SELECT DISTINCT(softwares.NAME) FROM softwares " .
                "LEFT JOIN hardware ON hardware.OSNAME = softwares.NAME " .
                "WHERE hardware.OSNAME IS NULL";
        }

        return $this->retrieve($l_query);
    }

    /**
     * Get all network info
     *
     * @param      $p_hardwareID
     * @param bool $p_snmp
     *
     * @return isys_component_dao_result
     */
    public function getNetworkAdapter($p_hardwareID, $p_snmp = false)
    {
        if ($p_snmp) {
            $l_sql = "SELECT main.*
				FROM  `snmp_networks` AS main
				WHERE main.SNMP_ID = " . $this->convert_sql_id($p_hardwareID) . " ORDER BY main.SLOT ASC;";

            return $this->retrieve($l_sql);
        } else {
            return $this->retrieve("SELECT networks.*, hardware.WORKGROUP AS domain FROM networks LEFT JOIN hardware ON hardware.ID = HARDWARE_ID WHERE HARDWARE_ID = " .
                $this->convert_sql_id($p_hardwareID));
        }
    }

    /**
     * Get snmp interfaces
     *
     * @param $p_snmpID
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function getSNMPNetworkInterfaces($p_snmpID)
    {
        $l_sql = "SELECT * FROM snmp_cards WHERE SNMP_ID = " . $this->convert_sql_id($p_snmpID);

        return $this->retrieve($l_sql);
    }

    /**
     * Gets connected port only for snmp
     *
     * @param $p_device_name
     * @param $p_port_name
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_database
     */
    public function getNetworkConnectedTo($p_device_name, $p_port_name)
    {
        $l_sql = "SELECT s.ID, TRIM(sn.MACADDR) AS connected_to FROM snmp AS s " . "INNER JOIN snmp_networks AS sn ON sn.SNMP_ID = s.ID " . "WHERE s.NAME = " .
            $this->convert_sql_text($p_device_name) . " AND sn.SLOT = " . $this->convert_sql_text($p_port_name);
        $l_row = $this->retrieve($l_sql)
            ->get_row();

        return $l_row;
    }

    /**
     * Get ports
     *
     * @param      $p_hardwareID
     * @param bool $p_snmp
     *
     * @return isys_component_dao_result
     */
    public function getPorts($p_hardwareID, $p_snmp = false)
    {
        if ($p_snmp) {
            return $this->retrieve("SELECT * FROM snmp_ports WHERE SNMP_ID = " . $this->convert_sql_id($p_hardwareID));
        } else {
            return $this->retrieve("SELECT * FROM ports WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
        }
    }

    /**
     * Get storage info
     *
     * @param      $p_hardwareID
     * @param bool $p_snmp
     *
     * @return isys_component_dao_result
     */
    public function getStorage($p_hardwareID, $p_snmp = false)
    {
        if ($p_snmp) {
            return $this->retrieve("SELECT * FROM snmp_storages WHERE SNMP_ID = " . $this->convert_sql_id($p_hardwareID));
        } else {
            return $this->retrieve("SELECT * FROM storages WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
        }
    }

    /**
     * Get drive info
     *
     * @param $p_hardwareID
     *
     * @return isys_component_dao_result
     */
    public function getDrives($p_hardwareID)
    {
        return $this->retrieve("SELECT * FROM drives WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
    }

    /**
     * Get virtual machine info
     *
     * @param $p_hardwareID
     *
     * @return isys_component_dao_result
     */
    public function getVirtualMachines($p_hardwareID)
    {
        return $this->retrieve("SELECT * FROM virtualmachines WHERE HARDWARE_ID = " . $this->convert_sql_id($p_hardwareID));
    }


    //SNMP Area

    /**
     * Get CPU info.
     *
     * @param  integer $p_hardwareID
     * @param  boolean $p_snmp
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getCPU($p_hardwareID, $p_snmp = false)
    {
        if ($p_snmp) {
            // @see ID-5576
            $cpuTableExist = $this->retrieve("SHOW TABLES LIKE 'snmp_cpus';");

            if (!is_countable($cpuTableExist) || !count($cpuTableExist)) {
                // Return the result since it's empty and the logic can work with that (instead of false, null, etc.).
                return $cpuTableExist;
            }

            return $this->retrieve('SELECT * FROM snmp_cpus WHERE SNMP_ID = ' . $this->convert_sql_id($p_hardwareID) . ';');
        } else {
            // @see ID-5576
            $cpuTableExist = $this->retrieve("SHOW TABLES LIKE 'cpus';");

            if (!is_countable($cpuTableExist) || !count($cpuTableExist)) {
                // Return the result since it's empty and the logic can work with that (instead of false, null, etc.).
                return $cpuTableExist;
            }

            return $this->retrieve('SELECT * FROM cpus WHERE HARDWARE_ID = ' . $this->convert_sql_id($p_hardwareID) . ';');
        }
    }

    /**
     * Get all SNMP devices.
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getHardwareSnmp()
    {
        return $this->retrieve("SELECT * FROM snmp LEFT JOIN snmp_accountinfo ON snmp.ID = snmp_accountinfo.SNMP_ID ORDER BY ID DESC");
    }

    /**
     * Get SNMP device id by hostname
     *
     * @param $p_arHostnames
     *
     * @return isys_component_dao_result
     */
    public function getHardwareSnmpIDs($p_arHostnames)
    {
        $l_query = "SELECT ID FROM snmp WHERE FALSE ";
        foreach ($p_arHostnames as $l_hostname) {
            $l_query .= " OR NAME = " . $this->convert_sql_text($l_hostname);
        }

        $l_query .= ' ORDER BY ID DESC';

        return $this->retrieve($l_query);
    }

    /**
     * Get all info of the snmp device by ID
     *
     * @param $p_hwid
     *
     * @return array
     */
    public function getHardwareItemBySNMP($p_hwid)
    {
        if ($this->does_snmp_exist()) {
            $l_res = $this->retrieve("SELECT snmp.*, snmp_accountinfo.*, CONCAT(GROUP_CONCAT(DISTINCT snmp_networks.MACADDR), ',', snmp.MACADDR) AS macaddr,
                (
                CASE
                    WHEN snmp_computers.SNMP_ID > 0 THEN 'server'
                    WHEN snmp_blades.SNMP_ID > 0 THEN 'blade'
                    WHEN snmp_printers.SNMP_ID > 0 THEN 'printer'
                    WHEN LOWER(snmp_switchinfos.TYPE) = 'switch' THEN 'switch'
                    WHEN snmp.TYPE = 'Network' THEN 'router'
                    WHEN LOWER(snmp.DESCRIPTION) REGEXP 'server' THEN 'server'
                    ELSE 'unknown'
                END
                ) AS OBJTYPE

                FROM snmp
                LEFT JOIN snmp_accountinfo ON snmp_accountinfo.SNMP_ID = snmp.ID
                LEFT JOIN snmp_networks ON snmp_networks.SNMP_ID = snmp.ID

                LEFT JOIN snmp_blades ON snmp_blades.SNMP_ID = snmp.ID
                LEFT JOIN snmp_computers ON snmp_computers.SNMP_ID = snmp.ID
                LEFT JOIN snmp_printers ON snmp_printers.SNMP_ID = snmp.ID
                LEFT JOIN snmp_switchinfos ON snmp_switchinfos.SNMP_ID = snmp.ID

                WHERE snmp.ID = " . $this->convert_sql_id($p_hwid) . "
                GROUP BY snmp.ID");

            return $l_res->get_row();
        } else {
            return [];
        }
    }

    /**
     * Retrieve powersupplies for snmp devices
     *
     * @param $p_snmp_id
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function getSNMPPowerSupplies($p_snmp_id)
    {
        $l_sql = "SELECT *, IF(SERIALNUMBER != '', CONCAT(REFERENCE, ' #SERIAL: ', SERIALNUMBER), REFERENCE) AS TITLE FROM snmp_powersupplies WHERE SNMP_ID = " .
            $this->convert_sql_id($p_snmp_id);

        return $this->retrieve($l_sql);
    }

    /**
     * Method to check if the current OCS Version is able to discover SNMP devices
     *
     * @return bool
     */
    public function does_snmp_exist()
    {
        return (bool)$this->retrieve('SHOW TABLES LIKE ' . $this->convert_sql_text('snmp'))
            ->num_rows();
    }

    /**
     * Retrieves Unique Mac-Address
     *
     * @param            $p_hardware_id
     * @param bool|false $p_snmp
     *
     * @return string
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_unique_mac_addresses($p_hardware_id, $p_snmp = false)
    {
        if ($p_snmp) {
            $l_table = 'snmp_networks';
            $l_id_field = 'SNMP_ID';
        } else {
            $l_table = 'networks';
            $l_id_field = 'HARDWARE_ID';
        }
        $l_sql = 'SELECT DISTINCT(MACADDR) AS mac FROM ' . $l_table . ' AS main WHERE ' . $l_id_field . ' = ' . $this->convert_sql_id($p_hardware_id);

        $l_res = $this->retrieve($l_sql);
        $l_return = [];
        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                $l_check = 'SELECT ID FROM ' . $l_table . ' WHERE ' . $l_id_field . ' != ' . $this->convert_sql_id($p_hardware_id) . ' AND MACADDR = ' .
                    $this->convert_sql_text($l_row['mac']);
                if ($this->retrieve($l_check)
                        ->num_rows() === 0) {
                    $l_return[] = $l_row['mac'];
                }
            }
        }

        return $l_return;
    }
}
