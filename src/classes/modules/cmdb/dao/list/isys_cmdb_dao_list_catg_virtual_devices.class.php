<?php

/**
 * i-doit
 * DAO: list for virtual devices
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_virtual_devices extends isys_component_dao_category_table_list
{

    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__VIRTUAL_DEVICE');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
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
        return isys_cmdb_dao_category_g_virtual_devices::instance($this->m_db)
            ->get_data(null, null, " AND isys_catg_virtual_device_list__isys_obj__id = " . $p_objID, null, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    }

    /**
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        if (isset($p_row["isys_catg_virtual_device_list__device_type"])) {
            $l_data = [];
            $db = isys_application::instance()->container->get('database');

            switch (intval($p_row["isys_catg_virtual_device_list__device_type"])) {
                case C__VIRTUAL_DEVICE__STORAGE:
                    $p_row["device_type"] = isys_application::instance()->container->get('language')
                        ->get("LC__CATG__STORAGE");

                    $l_dao_stor = new isys_cmdb_dao_category_g_stor($db);

                    // Retrieve host ressource.
                    if ($p_row["isys_virtual_device_host__isys_catg_stor_list__id"] > 0) {

                        // ----------- STORE ------------
                        $l_stordata = $l_dao_stor->get_data($p_row["isys_virtual_device_host__isys_catg_stor_list__id"])
                            ->__to_array();

                        // @see ID-2366 Don't display the object title.
                        $p_row["host_ressource"] = $l_stordata["isys_catg_stor_list__title"] . " (" .
                            isys_application::instance()->container->get('language')
                                ->get($l_stordata["isys_stor_manufacturer__title"]) . ")";
                    } elseif ($p_row["isys_virtual_device_host__isys_catg_ldevclient_list__id"] > 0) {

                        // ----------- LDEV ------------
                        $l_dao_ldevclient = new isys_cmdb_dao_category_g_ldevclient($db);
                        $l_ldevdata = $l_dao_ldevclient->get_data($p_row["isys_virtual_device_host__isys_catg_ldevclient_list__id"])
                            ->__to_array();

                        // @see ID-2366 Don't display the object title.
                        $p_row["host_ressource"] = $l_ldevdata["isys_catg_ldevclient_list__title"];
                    } elseif ($p_row["isys_virtual_device_host__isys_catg_drive_list__id"] > 0) {

                        // ----------- DRIVE ------------
                        $l_dao_drive = new isys_cmdb_dao_category_g_drive($db);
                        $l_drivedata = $l_dao_drive->get_data($p_row["isys_virtual_device_host__isys_catg_drive_list__id"])
                            ->__to_array();

                        // @see ID-2366 Don't display the object title.
                        $p_row["host_ressource"] = $l_drivedata["isys_catg_drive_list__title"] . " (" .
                            addslashes($l_drivedata["isys_catg_drive_list__driveletter"]) . ")";
                    } elseif ($p_row["isys_virtual_device_host__cluster_storage"]) {
                        $p_row["host_ressource"] = $p_row["isys_virtual_device_host__cluster_storage"];

                    // @see ID-5280
                    } elseif ($p_row["isys_virtual_device_host__isys_catg_raid_list__id"]) {
                        $l_dao_raid = new isys_cmdb_dao_category_g_raid($db);
                        $l_raiddata = $l_dao_raid->get_raids($p_row["isys_virtual_device_host__isys_catg_raid_list__id"], null, null)->__to_array();
                        $p_row["host_ressource"] = $l_raiddata["isys_catg_raid_list__title"];
                    }

                    // Retrieve local device.
                    if ($p_row["isys_virtual_device_local__isys_catg_stor_list__id"] > 0) {
                        $l_stordata = $l_dao_stor->get_data($p_row["isys_virtual_device_local__isys_catg_stor_list__id"])
                            ->__to_array();
                        $p_row["local_device"] = $l_stordata["isys_catg_stor_list__title"];
                    }

                    // Retrieve Type.
                    $p_row["type"] = isys_application::instance()->container->get('language')
                        ->get($p_row["isys_virtual_storage_type__title"]);

                    break;

                case C__VIRTUAL_DEVICE__NETWORK:
                    $p_row["device_type"] = isys_application::instance()->container->get('language')
                        ->get("LC__CMDB__CATG__NETWORK");

                    // DAO Init.
                    $l_dao = new isys_cmdb_dao_category_g_network_port($db);

                    // Retrieve host ressource.
                    if ($p_row["isys_virtual_device_host__isys_catg_port_list__id"] > 0) {
                        $l_data = $l_dao->get_data($p_row["isys_virtual_device_host__isys_catg_port_list__id"])
                            ->__to_array();

                        // @see ID-2366 Don't display the object title.
                        $p_row["host_ressource"] = $l_data["isys_catg_port_list__title"];
                    } elseif (isset($p_row["isys_virtual_device_host__switch_port_group"]) && $p_row["isys_virtual_device_host__switch_port_group"]) {
                        $p_row["host_ressource"] = "Switch Port Group: " . $p_row["isys_virtual_device_host__switch_port_group"];
                    }

                    // Retrieve local device.
                    if ($p_row["isys_virtual_device_local__isys_catg_port_list__id"] > 0) {
                        $l_data = $l_dao->get_data($p_row["isys_virtual_device_local__isys_catg_port_list__id"])
                            ->__to_array();
                        $p_row["local_device"] = $l_data["isys_catg_port_list__title"];
                    }

                    // Retrieve Type.
                    $p_row["type"] = isys_application::instance()->container->get('language')
                        ->get($p_row["isys_virtual_network_type__title"]);

                    break;

                case C__VIRTUAL_DEVICE__INTERFACE:
                    $p_row["device_type"] = isys_application::instance()->container->get('language')
                        ->get("LC__CMDB__CATG__UNIVERSAL_INTERFACE");

                    // DAO Init.
                    $l_dao = new isys_cmdb_dao_category_g_ui($db);

                    // Retrieve host ressource.
                    if ($p_row["isys_virtual_device_host__isys_catg_ui_list__id"] > 0) {
                        $l_data = $l_dao->get_data($p_row["isys_virtual_device_host__isys_catg_ui_list__id"])
                            ->__to_array();

                        // @see ID-2366 Don't display the object title.
                        $p_row["host_ressource"] = $l_data["isys_catg_ui_list__title"];
                    } elseif ($p_row["isys_virtual_device_host__cluster_ui"]) {
                        $p_row["host_ressource"] = $p_row["isys_virtual_device_host__cluster_ui"];
                    }

                    // Retrieve local ressource.
                    if ($p_row["isys_virtual_device_local__isys_catg_ui_list__id"] > 0) {
                        $l_data = $l_dao->get_data($p_row["isys_virtual_device_local__isys_catg_ui_list__id"])
                            ->__to_array();
                        $p_row["local_device"] = $l_data["isys_catg_ui_list__title"];
                    }

                    // Retrieve Type.
                    $p_row["type"] = isys_application::instance()->container->get('language')
                        ->get($l_data["isys_ui_con_type__title"]);
                    break;

                default:
                    $p_row["device_type"] = "Unknown";
                    break;
            }
        }
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "device_type"    => "LC__CMDB__CATG__VD__DEVICETYPE",
            "local_device"   => "LC__CMDB__CATG__VD__LOCAL_DEVICE",
            "host_ressource" => "LC__CMDB__CATG__VD__HOST_RESOURCE",
            "type"           => "LC__CMDB__CATG__VD__TYPE"
        ];
    }
}
