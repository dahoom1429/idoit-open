<?php

/**
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_virtual_devices extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param isys_cmdb_dao_category_g_virtual_devices $p_cat
     *
     * @return array|void
     * @throws Exception
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category_g_virtual_devices $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();
        $lang = isys_application::instance()->container->get('language');

        // Determine current device type.
        $l_device_type = $l_catdata['isys_catg_virtual_device_list__device_type'];

        if (is_numeric($l_device_type) && $l_device_type != -1) {
            $this->get_template_component()
                ->assign('device_type', $l_device_type);
        }

        // Storage DAO Init.
        $l_storage_dao = new isys_cmdb_dao_category_g_stor($p_cat->get_database_component());
        $l_ldev_dao = new isys_cmdb_dao_category_g_ldevclient($p_cat->get_database_component());
        $l_drive_dao = new isys_cmdb_dao_category_g_drive($p_cat->get_database_component());
        $l_clustermembers_dao = new isys_cmdb_dao_category_g_cluster_members($p_cat->get_database_component());
        $l_virtual_switch_dao = new isys_cmdb_dao_category_g_virtual_switch($p_cat->get_database_component());
        $l_raid_dao = new isys_cmdb_dao_category_g_raid($p_cat->get_database_component());

        // Get HOST SYSTEM.
        $l_vm = new isys_cmdb_dao_category_g_virtual_machine($p_cat->get_database_component());
        $l_host_system = $l_vm->get_host_system($_GET[C__CMDB__GET__OBJECT]);
        $l_host_system_type = $p_cat->get_objTypeID($l_host_system);

        // LOCAL.
        $l_local_storage = [];
        $l_stordata = $l_storage_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_stordata->get_row()) {
            $l_local_storage[$l_row['isys_catg_stor_list__id']] = $l_row['isys_catg_stor_list__title'] . ' (' . $lang->get($l_row['isys_stor_manufacturer__title']) . ')';
        }
        $l_rules['C__CMDB__CATG__VD__LOCAL_STORAGE']['p_arData'] = $l_local_storage;
        $l_rules['C__CMDB__CATG__VD__LOCAL_STORAGE']['p_strSelectedID'] = $l_catdata['isys_virtual_device_local__isys_catg_stor_list__id'];

        // Local Interfaces.
        $l_interface_dao = new isys_cmdb_dao_category_g_ui($p_cat->get_database_component());
        $l_interfaces = $l_interface_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);
        $l_arLocalIfaces = [];

        while ($l_row = $l_interfaces->get_row()) {
            $l_arLocalIfaces[$l_row['isys_catg_ui_list__id']] = $l_row['isys_catg_ui_list__title'];
        }

        $l_rules['C__CMDB__CATG__VD__LOCAL_INTERFACE']['p_arData'] = $l_arLocalIfaces;

        // HOST
        if ($l_host_system > 0) {
            $l_host_storage = [];
            $l_switches = [];
            $l_arHostIfaces = [];

            // ------------- CLUSTER ---------------

            if (defined('C__OBJTYPE__CLUSTER') && $l_host_system_type == C__OBJTYPE__CLUSTER) {
                $l_host_data = $l_vm->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL)
                    ->__to_array();

                $l_clustermembers = $l_clustermembers_dao->get_data(null, $l_host_system, '', null, C__RECORD_STATUS__NORMAL);
                $lunCache = [];
                $l_host_system = $l_host_data['isys_catg_virtual_machine_list__primary'];
                while ($l_row = $l_clustermembers->get_row()) {

                    // Fill stor list.
                    $l_stordata = $l_storage_dao->get_data(null, $l_row['isys_connection__isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    while ($l_row2 = $l_stordata->get_row()) {
                        $l_id = $l_row2['isys_catg_stor_list__id'] . '_' . defined_or_default('C__CATG__STORAGE', 'C__CATG__STORAGE');

                        if ($l_row2['isys_catg_stor_list__id'] == $l_catdata['isys_virtual_device_host__isys_catg_stor_list__id']) {
                            $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $l_id;
                            $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] =
                                $l_row2['isys_catg_stor_list__title'] . ' (' . $lang->get($l_row2['isys_stor_manufacturer__title']) . ')';
                        }

                        // @see  ID-3068  Changed the optgroup to the dynamic 'stor type' or the category name from 'storage'.
                        // $l_host_storage[$l_row['memberTitle']][$l_id]

                        // @see ID-2366 Don't display the object title.
                        $l_host_storage[$lang->get($l_row2['isys_stor_type__title'] ?: 'LC__CATG__STORAGE')][$l_id] = $l_row2['isys_catg_stor_list__title'] . ' (' . $lang->get($l_row2['isys_stor_manufacturer__title']) . ')';
                    }

                    // Fill ldev client list.
                    $l_ldevdata = $l_ldev_dao->get_data(null, $l_row['isys_connection__isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    while ($l_row2 = $l_ldevdata->get_row()) {
                        $l_id = $l_row2['isys_catg_ldevclient_list__id'] . '_' . defined_or_default('C__CATG__LDEV_CLIENT', 'C__CATG__LDEV_CLIENT');

                        // @see ID-7389 only show LUNS once
                        if (isset($lunCache[$l_row2['isys_catg_ldevclient_list__title']])) {
                            continue;
                        }

                        $lunCache[$l_row2['isys_catg_ldevclient_list__title']] = true;
                        $l_host_storage[$lang->get('LC__CMDB__CATG__LDEV_CLIENT')][$l_id] = $l_row2['isys_catg_ldevclient_list__title'];
                    }

                    // Fill drive list.
                    $l_drivedata = $l_drive_dao->get_data(null, $l_row['isys_connection__isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    while ($l_row2 = $l_drivedata->get_row()) {
                        $l_id = $l_row2['isys_catg_drive_list__id'] . '_' . defined_or_default('C__CATG__DRIVE', 'C__CATG__DRIVE');

                        if ($l_row2['isys_catg_drive_list__id'] == $l_catdata['isys_virtual_device_host__isys_catg_drive_list__id']) {
                            $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $l_id;
                            $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] =
                                $l_row2['isys_catg_drive_list__title'] . ' (' . $lang->get($l_row2['isys_catg_drive_list__driveletter']) . ')';
                        }

                        // @see ID-2366 Don't display the object title.
                        $l_host_storage[$lang->get('LC__STORAGE_DRIVE')][$l_id] = $l_row2['isys_catg_drive_list__title'] . ' (' . $lang->get($l_row2['isys_catg_drive_list__driveletter']) . ')';
                    }

                    // Fill raid list.
                    $l_host_storage[$lang->get('LC__CMDB__CATG__RAID')] = $p_cat->get_raid_list($l_host_system);
                    foreach ($l_host_storage[$lang->get('LC__CMDB__CATG__RAID')] as $raid_id => $raid) {
                        if ($raid_id == $l_catdata['isys_virtual_device_host__isys_catg_raid_list__id'] . '_' . defined_or_default('C__CATG__RAID', 'C__CATG__RAID')) {
                            $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] = $raid;
                            $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $raid_id;
                        }
                    }

                    // Virtual switches and port groups.
                    $l_virtual_switches = $l_virtual_switch_dao->get_data(null, $l_row['isys_connection__isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    while ($l_row2 = $l_virtual_switches->get_row()) {
                        $l_port_groups = $l_virtual_switch_dao->get_port_groups($l_row2['isys_catg_virtual_switch_list__id']);

                        while ($l_prow = $l_port_groups->get_row()) {
                            $l_switches[$l_row2['isys_catg_virtual_switch_list__title']][isys_glob_htmlentities($l_prow['isys_virtual_port_group__title'])] =
                                $l_prow['isys_virtual_port_group__title'];
                        }
                    }

                    $l_rules['C__CMDB__CATG__VD__SWITCH_PORT_GROUP']['p_strSelectedID'] = isys_glob_htmlentities($l_catdata['isys_virtual_device_host__switch_port_group']);

                    // Host Interfaces
                    $l_interfaces = $l_interface_dao->get_data(null, $l_row['isys_connection__isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    while ($l_row2 = $l_interfaces->get_row()) {
                        $l_arHostIfaces[$l_row2['isys_catg_ui_list__id']] = $l_row2['isys_obj__title'] . ' >> ' . $l_row2['isys_catg_ui_list__title'];
                    }
                }

                // @see  ID-2366  Only display ONE entry, despite warnings from the DEVs.
                foreach ($l_host_storage[$lang->get('LC__CMDB__CATG__LDEV_CLIENT')] as $id => $value) {
                    if ($value === $l_catdata['isys_virtual_device_host__cluster_storage']) {
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] = $value;
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $id;
                    }
                }

                krsort($l_host_storage);

                $l_rules['C__CMDB__CATG__VD__HOST_INTERFACE']['p_arData'] = $l_arHostIfaces;
                $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_arData'] = $l_host_storage;
                $l_rules['C__CMDB__CATG__VD__SWITCH_PORT_GROUP']['p_arData'] = $l_switches;
            } else {
                // ------------- SINGLE HOST ---------------

                // Fill stor list.
                $l_stordata = $l_storage_dao->get_data(null, $l_host_system, '', null, C__RECORD_STATUS__NORMAL);
                while ($l_row = $l_stordata->get_row()) {
                    $l_id = $l_row['isys_catg_stor_list__id'] . '_' . defined_or_default('C__CATG__STORAGE', 'C__CATG__STORAGE');

                    if ($l_row['isys_catg_stor_list__id'] == $l_catdata['isys_virtual_device_host__isys_catg_stor_list__id']) {
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $l_id;
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] =
                            $l_row['isys_catg_stor_list__title'] . ' (' . $lang->get($l_row['isys_stor_manufacturer__title']) . ')';
                    }

                    // @see ID-2366 Don't display the object title.
                    $l_host_storage[$lang->get('LC__STORAGE_DEVICE')][$l_id] = $l_row['isys_catg_stor_list__title'] . ' (' . $lang->get($l_row['isys_stor_manufacturer__title']) . ')';
                }

                // Fill ldev client list.
                $l_ldevdata = $l_ldev_dao->get_data(null, $l_host_system, '', null, C__RECORD_STATUS__NORMAL);
                while ($l_row = $l_ldevdata->get_row()) {
                    $l_id = $l_row['isys_catg_ldevclient_list__id'] . '_' . defined_or_default('C__CATG__LDEV_CLIENT', 'C__CATG__LDEV_CLIENT');

                    if ($l_row['isys_catg_ldevclient_list__id'] == $l_catdata['isys_virtual_device_host__isys_catg_ldevclient_list__id']) {
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $l_id;
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] =
                            $l_row['isys_catg_ldevclient_list__title'] . ' (' . $lang->get($l_row['isys_catg_sanpool_list__title']) . ')';
                    }

                    // @see ID-2366 Don't display the object title.
                    $l_host_storage[$lang->get('LC__CMDB__CATG__LDEV_CLIENT')][$l_id] = $l_row['isys_catg_ldevclient_list__title'] . ' (' . $lang->get($l_row['isys_catg_sanpool_list__title']) . ')';
                }

                // Fill drive list.
                $l_drivedata = $l_drive_dao->get_data(null, $l_host_system, '', null, C__RECORD_STATUS__NORMAL);
                while ($l_row = $l_drivedata->get_row()) {
                    $l_id = $l_row['isys_catg_drive_list__id'] . '_' . defined_or_default('C__CATG__DRIVE', 'C__CATG__DRIVE');

                    if ($l_row['isys_catg_drive_list__id'] == $l_catdata['isys_virtual_device_host__isys_catg_drive_list__id']) {
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] = $l_row['isys_catg_drive_list__title'] . ' (' .
                            $lang->get($l_row['isys_catg_drive_list__driveletter']) . ')';
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $l_id;
                    }

                    // @see ID-2366 Don't display the object title.
                    $l_host_storage[$lang->get('LC__STORAGE_DRIVE')][$l_id] = $l_row['isys_catg_drive_list__title'] . ' (' . $lang->get($l_row['isys_catg_drive_list__driveletter']) . ')';
                }

                // Fill raid list.
                $l_host_storage[$lang->get('LC__CMDB__CATG__RAID')] = $p_cat->get_raid_list($l_host_system);
                foreach ($l_host_storage[$lang->get('LC__CMDB__CATG__RAID')] as $raid_id => $raid) {
                    if ($raid_id == $l_catdata['isys_virtual_device_host__isys_catg_raid_list__id'] . '_' . defined_or_default('C__CATG__RAID', 'C__CATG__RAID')) {
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strValue'] = $raid;
                        $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_strSelectedID'] = $raid_id;
                    }
                }

                $l_rules['C__CMDB__CATG__VD__HOST_STORAGE']['p_arData'] = $l_host_storage;

                // Virtual switches and port groups.
                $l_virtual_switches = $l_virtual_switch_dao->get_data(null, $l_host_system, '', null, C__RECORD_STATUS__NORMAL);
                while ($l_row = $l_virtual_switches->get_row()) {
                    $l_port_groups = $l_virtual_switch_dao->get_port_groups($l_row['isys_catg_virtual_switch_list__id']);
                    while ($l_prow = $l_port_groups->get_row()) {
                        $l_switches[$l_row['isys_catg_virtual_switch_list__title']][isys_glob_htmlentities($l_prow['isys_virtual_port_group__title'])] = $l_prow['isys_virtual_port_group__title'];
                    }
                }

                $l_rules['C__CMDB__CATG__VD__SWITCH_PORT_GROUP']['p_strSelectedID'] = isys_glob_htmlentities($l_catdata['isys_virtual_device_host__switch_port_group']);
                $l_rules['C__CMDB__CATG__VD__SWITCH_PORT_GROUP']['p_arData'] = $l_switches;

                // Host Interfaces.
                $l_interfaces = $l_interface_dao->get_data(null, $l_host_system, '', null, C__RECORD_STATUS__NORMAL);
                while ($l_row = $l_interfaces->get_row()) {
                    $l_arHostIfaces[$l_row['isys_catg_ui_list__id']] = $l_row['isys_obj__title'] . ' >> ' . $l_row['isys_catg_ui_list__title'];
                }

                $l_rules['C__CMDB__CATG__VD__HOST_INTERFACE']['p_arData'] = $l_arHostIfaces;
            }
        }

        unset($l_arHostIfaces, $l_arLocalIfaces, $l_switches, $l_host_storage);

        // Switch device type and assign additional attributes.
        switch ($l_device_type) {
            case C__VIRTUAL_DEVICE__NETWORK:
                $l_rules['C__CMDB__CATG__VD__LOCAL_NETWORK_PORT']['p_strSelectedID'] = $l_catdata['isys_virtual_device_local__isys_catg_port_list__id'];
                $l_rules['C__CMDB__CATG__VD__HOST_NETWORK_PORT']['p_strSelectedID'] = $l_catdata['isys_virtual_device_host__isys_catg_port_list__id'];
                $l_rules['C__CMDB__CATG__VD__NETWORK_TYPE']['p_strSelectedID'] = $l_catdata['isys_virtual_device_local__isys_virtual_network_type__id'];
                $l_rules['C__CMDB__CATG__VD__SWITCH_PORT_GROUP']['p_strValue'] = $l_catdata['isys_virtual_device_host__switch_port_group'];

                /*
                 * We stick to the view logic making the last item in the list
                 * of virtual network types decide on whether to hide the switch
                 * port section.
                 */
                $this->get_template_component()
                    ->assign('static_device_type', $l_catdata['isys_virtual_device_local__isys_virtual_network_type__id']);

                break;
            case C__VIRTUAL_DEVICE__STORAGE:
                $l_rules['C__CMDB__CATG__VD__DISK_IMAGE_LOCATION']['p_strValue'] = $l_catdata['isys_catg_virtual_device_list__disk_image_location'];
                $l_rules['C__CMDB__CATG__VD__STORAGE_TYPE']['p_strSelectedID'] = $l_catdata['isys_virtual_device_local__isys_virtual_storage_type__id'];

                break;
            case C__VIRTUAL_DEVICE__INTERFACE:
                $l_rules['C__CMDB__CATG__VD__LOCAL_INTERFACE']['p_strSelectedID'] = $l_catdata['isys_virtual_device_local__isys_catg_ui_list__id'];

                if ($l_catdata['isys_virtual_device_host__isys_catg_ui_list__id']) {
                    $l_rules['C__CMDB__CATG__VD__HOST_INTERFACE']['p_strSelectedID'] = $l_catdata['isys_virtual_device_host__isys_catg_ui_list__id'];
                } else {
                    $l_rules['C__CMDB__CATG__VD__HOST_INTERFACE']['p_strSelectedID'] = $l_catdata['isys_virtual_device_host__cluster_ui'];
                }

                break;
        }

        /**
         * ------------------------------------------------------------------------------------
         * NON COMPLEX ASSIGNMENTS
         * ------------------------------------------------------------------------------------
         */

        // Assign cleartext values.
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_catg_virtual_device_list__description'];

        $l_local_ports = [];
        $l_host_ports = [];

        if (isset($_GET[C__CMDB__GET__OBJECT]) && $_GET[C__CMDB__GET__OBJECT] > 0) {
            // Get the ports to disply inside the drop-down fields.
            $l_port_dao = new isys_cmdb_dao_category_g_network_port($p_cat->get_database_component());
            $l_port_res = $l_port_dao->get_ports($_GET[C__CMDB__GET__OBJECT], null, C__RECORD_STATUS__NORMAL);

            while ($l_port_row = $l_port_res->get_row()) {
                $l_port = $l_port_row['isys_catg_port_list__title'];

                if ($l_port_row['isys_catg_netp_list__title'] !== null) {
                    $l_port .= ' (' . $l_port_row['isys_catg_netp_list__title'] . ')';
                } elseif ($l_port_row['isys_catg_hba_list__title'] !== null) {
                    $l_port .= ' (' . $l_port_row['isys_catg_hba_list__title'] . ')';
                }

                if ($l_port_row['isys_cats_net_ip_addresses_list__title'] !== null) {
                    $l_port .= ' (' . $l_port_row['isys_cats_net_ip_addresses_list__title'] . ')';
                }

                if ($l_port == '') {
                    $l_port = $l_port_row['isys_catg_port_list__mac'];
                }

                $l_local_ports[$l_port_row['isys_catg_port_list__id']] = $l_port == '' ? isys_tenantsettings::get('gui.empty_value', '-') : $l_port;
            }
        }

        if ($l_host_system > 0) {
            $l_port_res = $l_port_dao->get_ports($l_host_system, null, C__RECORD_STATUS__NORMAL);

            while ($l_port_row = $l_port_res->get_row()) {
                $l_port = $l_port_row['isys_catg_port_list__title'];

                if ($l_port_row['isys_catg_netp_list__title'] !== null) {
                    $l_port .= ' (' . $l_port_row['isys_catg_netp_list__title'] . ')';
                } elseif ($l_port_row['isys_catg_hba_list__title'] !== null) {
                    $l_port .= ' (' . $l_port_row['isys_catg_hba_list__title'] . ')';
                }

                if ($l_port_row['isys_cats_net_ip_addresses_list__title'] !== null) {
                    $l_port .= ' (' . $l_port_row['isys_cats_net_ip_addresses_list__title'] . ')';
                }

                $l_host_ports[$l_port_row['isys_catg_port_list__id']] = $l_port;
            }
        }

        asort($l_local_ports);
        asort($l_host_ports);

        $l_rules['C__CMDB__CATG__VD__LOCAL_NETWORK_PORT']['p_arData'] = $l_local_ports;
        $l_rules['C__CMDB__CATG__VD__HOST_NETWORK_PORT']['p_arData'] = $l_host_ports;

        if (!$p_cat->get_validation()) {
            $l_rules['C__CMDB__CATG__VD__NETWORK_TYPE']['p_strSelectedID'] = $_POST['C__CMDB__CATG__VD__NETWORK_TYPE'];
            $l_rules['C__CMDB__CATG__VD__LOCAL_NETWORK_PORT']['p_strSelectedID'] = $_POST['C__CMDB__CATG__VD__LOCAL_NETWORK_PORT'];
            $l_rules['C__CMDB__CATG__VD__HOST_NETWORK_PORT']['p_strSelectedID'] = $_POST['C__CMDB__CATG__VD__HOST_NETWORK_PORT'];
            $l_rules['C__CMDB__CATG__VD__DISK_IMAGE_LOCATION']['p_strValue'] = $_POST['C__CMDB__CATG__VD__DISK_IMAGE_LOCATION'];
            $l_rules['C__CMDB__CATG__VD__STORAGE_TYPE']['p_strSelectedID'] = $_POST['C__CMDB__CATG__VD__STORAGE_TYPE'];
            $l_rules['C__CMDB__CATG__VD__LOCAL_STORAGE']['p_strSelectedID'] = $_POST['C__CMDB__CATG__VD__LOCAL_STORAGE'];
            $l_rules['C__CMDB__CATG__VD__LOCAL_INTERFACE']['p_strSelectedID'] = $_POST['C__CMDB__CATG__VD__LOCAL_INTERFACE'];
        }

        if ($_POST[C__GET__NAVMODE] != C__NAVMODE__EDIT && $_GET[C__GET__NAVMODE] != C__NAVMODE__EDIT) {
            $this->get_template_component()
                ->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1');
        }


        $ajaxUrlPars = $_GET;
        $ajaxUrlPars[C__GET__AJAX] = 1;
        $ajaxUrlPars[C__GET__AJAX_CALL] = 'category';
        $ajaxUrlPars[C__CMDB__GET__CATLEVEL] = $_GET[C__CMDB__GET__CATLEVEL];

        $this->get_template_component()
            ->assign('virtual_device_ajax_url', isys_helper_link::create_url($ajaxUrlPars))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_g_virtual_devices &$p_cat
     * @param   array                                    $p_get_param_override
     * @param   string                                   $p_strVarName
     * @param   string                                   $p_strTemplateName
     * @param   boolean                                  $p_bCheckbox
     * @param   boolean                                  $p_bOrderLink
     * @param   string                                   $p_db_field_name
     *
     * @return  mixed
     * @throws  isys_exception_general
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, 'isys_catg_virtual_device_list__id');
    }
}
