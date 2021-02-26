<?php

use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;

define("C__VIRTUAL_DEVICE__NETWORK", 1);
define("C__VIRTUAL_DEVICE__STORAGE", 2);
define("C__VIRTUAL_DEVICE__INTERFACE", 3);
define("C__VIRTUAL_DEVICE__UNASSOCIATED", -1);

/**
 * i-doit
 *
 * DAO: global category for virtual devices
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_virtual_devices extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_devices';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__VIRTUAL_DEVICE';

    /**
     * Category's identifier.
     *
     * @var    int
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__VIRTUAL_DEVICE;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Category's source table.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_virtual_device_list';

    /**
     * Gets device types as array.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_device_types()
    {
        return [
            C__VIRTUAL_DEVICE__NETWORK   => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__NETWORK'),
            C__VIRTUAL_DEVICE__STORAGE   => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__VD__LOCAL_DEVICE'),
            C__VIRTUAL_DEVICE__INTERFACE => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__VD__LOCAL_INTERFACE')
        ];
    }

    /**
     * Callback method for the storage host device dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_host_stor_device(isys_request $p_request)
    {
        $l_return = null;
        $l_obj_id = $p_request->get_object_id();
        $l_dao_vm = new isys_cmdb_dao_category_g_virtual_machine($this->get_database_component());
        $l_host_obj_id = $l_dao_vm->get_host_system($l_obj_id);

        $typeId = $l_dao_vm->get_objTypeID($l_host_obj_id);
        if ($typeId == defined_or_default('C__OBJTYPE__CLUSTER')) {
            $l_dao_cm = new isys_cmdb_dao_category_g_cluster_members($this->get_database_component());
            $l_res = $l_dao_cm->get_assigned_members($l_host_obj_id);
            $l_return = null;
            while ($l_row = $l_res->get_row()) {
                if (!empty($l_return)) {
                    $l_puffer = $this->get_dialog_stor_content_by_host($l_row['isys_obj__id']);
                    if (!empty($l_puffer)) {
                        $l_return = array_merge_recursive($l_return, $l_puffer);
                    }
                } else {
                    $l_return = $this->get_dialog_stor_content_by_host($l_row['isys_obj__id']);
                }
            }
        } else {
            if ($l_host_obj_id > 0) {
                $l_return = $this->get_dialog_stor_content_by_host($l_host_obj_id);
            }
        }

        return $l_return;
    }

    /**
     * Callback method for the storage host device dialog-field.
     *
     * @param   isys_request $p_request
     * @param   string       $p_type
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_local_host_port(isys_request $p_request, $p_type)
    {
        $l_return = [];

        if (is_array($p_type) && count($p_type) == 1) {
            $p_type = $p_type[0];
        }

        $l_dao_vm = new isys_cmdb_dao_category_g_virtual_machine($this->get_database_component());
        $l_dao_port = new isys_cmdb_dao_category_g_network_port($this->get_database_component());

        if ($p_type == 'host') {
            $l_host_obj_id = $l_dao_vm->get_host_system($p_request->get_object_id());
        } else {
            $l_host_obj_id = $p_request->get_object_id();
        }

        if ($l_host_obj_id > 0) {
            $l_res = $l_dao_port->get_ports($l_host_obj_id);
            while ($l_port_row = $l_res->get_row()) {
                $l_return[$l_port_row['isys_catg_port_list__id']] = $l_port_row['isys_catg_port_list__title'];
            }
        }

        return $l_return;
    }

    /**
     * Callback method for the interface host dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_host_interface(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        $l_dao_vm = new isys_cmdb_dao_category_g_virtual_machine($this->get_database_component());
        $l_dao_ui = new isys_cmdb_dao_category_g_ui($this->get_database_component());

        $l_host_obj_id = $l_dao_vm->get_host_system($l_obj_id);

        $l_return = null;
        if ($l_dao_vm->get_objTypeID($l_host_obj_id) == defined_or_default('C__OBJTYPE__CLUSTER')) {
            $l_dao_cm = new isys_cmdb_dao_category_g_cluster_members($this->get_database_component());
            $l_res = $l_dao_cm->get_assigned_members($l_host_obj_id);
            $l_return = null;
            while ($l_row = $l_res->get_row()) {
                $l_interfaces = $l_dao_ui->get_data(null, $l_row["isys_connection__isys_obj__id"]);
                while ($l_row2 = $l_interfaces->get_row()) {
                    $l_return[$l_row2["isys_catg_ui_list__id"]] = $l_row["isys_obj__title"] . " >> " . $l_row2["isys_catg_ui_list__title"];
                }
            }
        } else {
            $l_interfaces = $l_dao_ui->get_data(null, $l_obj_id);
            while ($l_row2 = $l_interfaces->get_row()) {
                $l_return[$l_row2["isys_catg_ui_list__id"]] = $l_row2["isys_obj__title"] . " >> " . $l_row2["isys_catg_ui_list__title"];
            }
        }

        return $l_return;
    }

    /**
     *  Callback method for normal dialog-fields in this category.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_general(isys_request $p_request, $p_class)
    {
        $l_obj_id = $p_request->get_object_id();

        if (is_array($p_class) && count($p_class) == 1) {
            $p_class = $p_class[0];
        }

        $l_return = null;

        if (class_exists($p_class)) {
            $l_dao_general = new $p_class($this->get_database_component());
            $l_class_table = $l_dao_general->get_source_table();
            $l_class_table = (is_int(strpos($l_class_table, 'list'))) ? $l_class_table : ((is_int(strpos($l_class_table, '_2_'))) ? $l_class_table : $l_class_table .
                '__list');

            $l_res = $l_dao_general->get_data(null, $l_obj_id);
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row[$l_class_table . '__id']] = $l_row[$l_class_table . '__title'];
            }
        }

        return $l_return;
    }

    public function callback_property_switch_port_group(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        $l_dao_vm = new isys_cmdb_dao_category_g_virtual_machine($this->get_database_component());
        $l_dao_vs = new isys_cmdb_dao_category_g_virtual_switch($this->get_database_component());

        $l_host_obj_id = $l_dao_vm->get_host_system($l_obj_id);

        $l_return = null;
        if ($l_dao_vm->get_objTypeID($l_host_obj_id) == defined_or_default('C__OBJTYPE__CLUSTER')) {
            $l_dao_cm = new isys_cmdb_dao_category_g_cluster_members($this->get_database_component());
            $l_res = $l_dao_cm->get_assigned_members($l_host_obj_id);
            $l_return = null;
            while ($l_row = $l_res->get_row()) {
                $l_res2 = $l_dao_vs->get_data(null, $l_row["isys_connection__isys_obj__id"]);
                while ($l_row2 = $l_res2->get_row()) {
                    $l_return[$l_row2["isys_catg_virtual_switch_list__title"]][$l_row2["isys_virtual_port_group__title"]] = $l_row2["isys_virtual_port_group__title"];
                }
            }
        } else {
            $l_res = $l_dao_vs->get_data(null, $l_obj_id);
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row["isys_catg_virtual_switch_list__title"]][$l_row["isys_virtual_port_group__title"]] = $l_row["isys_virtual_port_group__title"];
            }
        }

        return $l_return;
    }

    /**
     * Helper method to get the content for the dialog list assigned storage devices.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_dialog_content_by_host($p_obj_id)
    {
        $l_host_storage = [];
        $l_dao_stor = new isys_cmdb_dao_category_g_stor($this->get_database_component());
        $l_dao_ldev = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());
        $l_dao_driv = new isys_cmdb_dao_category_g_drive($this->get_database_component());

        $l_res = $l_dao_stor->get_data(null, $p_obj_id);
        while ($l_row = $l_res->get_row()) {
            $l_id = $l_row["isys_catg_stor_list__id"] . "," . defined_or_default('C__CATG__STORAGE');
            $l_host_storage[isys_application::instance()->container->get('language')
                ->get('LC__STORAGE_DEVICE')][$l_id] = $l_row["isys_catg_stor_list__title"] . " (" . isys_application::instance()->container->get('language')
                    ->get($l_row["isys_stor_manufacturer__title"]) . ")";
        }

        $l_res = $l_dao_ldev->get_data(null, $p_obj_id);
        while ($l_row = $l_res->get_row()) {
            $l_id = $l_row["isys_catg_ldevclient_list__id"] . "," . defined_or_default('C__CATG__LDEV_CLIENT');
            $l_host_storage[isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__LDEV_CLIENT')][$l_id] = $l_row["isys_catg_ldevclient_list__title"] . " (" . isys_application::instance()->container->get('language')
                    ->get($l_row["isys_catg_sanpool_list__title"]) . ")";
        }

        $l_res = $l_dao_driv->get_data(null, $p_obj_id);
        while ($l_row = $l_res->get_row()) {
            $l_id = $l_row["isys_catg_drive_list__id"] . "," . defined_or_default('C__CATG__DRIVE');
            $l_host_storage[isys_application::instance()->container->get('language')
                ->get('LC__STORAGE_DRIVE')][$l_id] = $l_row["isys_catg_drive_list__title"] . " (" . isys_application::instance()->container->get('language')
                    ->get($l_row["isys_catg_drive_list__driveletter"]) . ")";
        }

        return $l_host_storage;
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_virtual_device_list " .
            "INNER JOIN isys_virtual_device_local ON isys_virtual_device_local__isys_catg_virtual_device_list__id = isys_catg_virtual_device_list__id " .
            "LEFT JOIN isys_virtual_device_host ON isys_virtual_device_host__isys_catg_virtual_device_list__id = isys_catg_virtual_device_list__id " .
            "LEFT JOIN isys_virtual_storage_type ON isys_virtual_device_local__isys_virtual_storage_type__id = isys_virtual_storage_type__id " .
            "LEFT JOIN isys_virtual_network_type ON isys_virtual_device_local__isys_virtual_network_type__id = isys_virtual_network_type__id " .
            "INNER JOIN isys_obj ON isys_catg_virtual_device_list__isys_obj__id = isys_obj__id " . "WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter);

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND isys_catg_virtual_device_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if (!empty($p_status)) {
            $l_sql .= " AND isys_catg_virtual_device_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Dynamic properties mainly used for report manager
     *
     * @return array|void
     */
    protected function dynamic_properties()
    {
        return [
            '_host_resource' => new DynamicProperty(
                'LC__CMDB__CATG__VD__HOST_RESOURCE',
                'isys_catg_virtual_device_list__id',
                'isys_catg_virtual_device_list',
                [
                    $this,
                    'getHostResource'
                ]
            ),
            '_type' => new DynamicProperty(
                'LC__CMDB__CATG__VD__TYPE',
                'isys_catg_virtual_device_list__id',
                'isys_catg_virtual_device_list',
                [
                    $this,
                    'getVirtualDeviceType'
                ]
            ),
            '_local_device' => new DynamicProperty(
                'LC__CMDB__CATG__VD__LOCAL_DEVICE',
                'isys_catg_virtual_device_list__id',
                'isys_catg_virtual_device_list',
                [
                    $this,
                    'getLocalResource'
                ]
            ),
        ];
    }

    /**
     * Get local resource of the entry
     *
     * @param $data
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function getLocalResource($data)
    {
        $dao = isys_cmdb_dao_category_g_virtual_devices::instance(isys_application::instance()->container->database);

        $query = 'SELECT isys_catg_stor_list__title AS \'resourceTitle\' FROM isys_virtual_device_local 
            INNER JOIN isys_catg_stor_list ON isys_catg_stor_list__id = isys_virtual_device_local__isys_catg_stor_list__id
            WHERE isys_virtual_device_local__isys_catg_virtual_device_list__id = ' . $dao->convert_sql_id($data['isys_catg_virtual_device_list__id']) . '
            UNION
            SELECT isys_catg_port_list__title AS \'resourceTitle\' FROM isys_virtual_device_local 
            INNER JOIN isys_catg_port_list ON isys_catg_port_list__id = isys_virtual_device_local__isys_catg_port_list__id
            WHERE isys_virtual_device_local__isys_catg_virtual_device_list__id = ' . $dao->convert_sql_id($data['isys_catg_virtual_device_list__id']) . '
            UNION
            SELECT isys_catg_ui_list__title AS \'resourceTitle\' FROM isys_virtual_device_local 
            INNER JOIN isys_catg_ui_list ON isys_catg_ui_list__id = isys_virtual_device_local__isys_catg_ui_list__id
            WHERE isys_virtual_device_local__isys_catg_virtual_device_list__id =' . $dao->convert_sql_id($data['isys_catg_virtual_device_list__id']);

        $localData = $dao->retrieve($query)->get_row();
        return empty($localData['resourceTitle']) ? isys_tenantsettings::get('gui.empty_value', '-') : $localData['resourceTitle'];
    }

    /**
     * Get host resource of the entry
     *
     * @param $data
     *
     * @return string
     * @throws isys_exception_database
     */
    public function getHostResource($data)
    {
        $dao = isys_cmdb_dao_category_g_virtual_devices::instance(isys_application::instance()->container->database);

        $query = 'SELECT * FROM isys_virtual_device_local
          INNER JOIN isys_catg_virtual_device_list ON isys_catg_virtual_device_list__id = isys_virtual_device_local__isys_catg_virtual_device_list__id  
          WHERE isys_catg_virtual_device_list__id = ' . $dao->convert_sql_id($data['isys_catg_virtual_device_list__id']);
        $virtualDeviceData = $dao->retrieve($query)->get_row();

        if (!$virtualDeviceData['isys_catg_virtual_device_list__id']) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $type = ($virtualDeviceData['isys_virtual_device_local__isys_virtual_network_type__id'] ?: ($virtualDeviceData['isys_virtual_device_local__isys_virtual_storage_type__id'] ?: null));

        switch ($virtualDeviceData['isys_catg_virtual_device_list__device_type']) {
            case C__VIRTUAL_DEVICE__INTERFACE:
                return $this->getHostInterfaceDevice($virtualDeviceData['isys_catg_virtual_device_list__id']);
                break;
            case C__VIRTUAL_DEVICE__NETWORK:

                return $this->getHostNetworkDevice($virtualDeviceData['isys_catg_virtual_device_list__id'], $type);
                break;
            case C__VIRTUAL_DEVICE__STORAGE:
                return $this->getHostStorageDevice($virtualDeviceData['isys_catg_virtual_device_list__id']);
                break;
            default:
                break;
        }
    }

    /**
     * Get Type of the specified specified entry
     *
     * @param $data
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function getVirtualDeviceType($data)
    {
        $dao = isys_cmdb_dao_category_g_virtual_devices::instance(isys_application::instance()->container->database);
        $query = 'SELECT (CASE 
            WHEN isys_ui_con_type__id > 0 THEN isys_ui_con_type__title
            WHEN isys_virtual_network_type__id > 0 THEN isys_virtual_network_type__title
            WHEN isys_virtual_storage_type__id > 0 THEN isys_virtual_storage_type__title
            ELSE \'' . isys_tenantsettings::get('gui.empty_value', '-') . '\'
          END) AS type FROM isys_virtual_device_local
          LEFT JOIN isys_catg_ui_list ON isys_catg_ui_list__id = isys_virtual_device_local__isys_catg_ui_list__id
          LEFT JOIN isys_ui_con_type ON isys_ui_con_type__id = isys_catg_ui_list__isys_ui_con_type__id
          LEFT JOIN isys_virtual_network_type ON isys_virtual_network_type__id = isys_virtual_device_local__isys_virtual_network_type__id  
          LEFT JOIN isys_virtual_storage_type ON isys_virtual_storage_type__id = isys_virtual_device_local__isys_virtual_storage_type__id
          WHERE isys_virtual_device_local__isys_catg_virtual_device_list__id = ' . $dao->convert_sql_id($data['isys_catg_virtual_device_list__id']);

        return $dao->retrieve($query)->get_row_value('type');
    }

    /**
     * Get host storage device
     *
     * @param $data
     *
     * @return string
     */
    private function getHostStorageDevice($id)
    {
        $query = 'SELECT isys_obj__id AS \'objId\', isys_obj__title AS \'objTitle\', isys_catg_stor_list__title AS \'resourceTitle\' FROM isys_virtual_device_host 
            INNER JOIN isys_catg_stor_list ON isys_catg_stor_list__id = isys_virtual_device_host__isys_catg_stor_list__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_stor_list__isys_obj__id
            WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' . $this->convert_sql_id($id) . '
            UNION
            SELECT isys_obj__id AS \'objId\', isys_obj__title AS \'objTitle\', isys_catg_drive_list__title AS \'resourceTitle\' FROM isys_virtual_device_host 
            INNER JOIN isys_catg_drive_list ON isys_catg_drive_list__id = isys_virtual_device_host__isys_catg_drive_list__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_drive_list__isys_obj__id
            WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' . $this->convert_sql_id($id) . '
            UNION
            SELECT isys_obj__id AS \'objId\', isys_obj__title AS \'objTitle\', isys_catg_ldevclient_list__title AS \'resourceTitle\' FROM isys_virtual_device_host 
            INNER JOIN isys_catg_ldevclient_list ON isys_catg_ldevclient_list__id = isys_virtual_device_host__isys_catg_ldevclient_list__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_ldevclient_list__isys_obj__id
            WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' . $this->convert_sql_id($id) . '
            UNION
            SELECT isys_obj__id AS \'objId\', isys_obj__title AS \'objTitle\', isys_catg_raid_list__title AS \'resourceTitle\' FROM isys_virtual_device_host 
            INNER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_virtual_device_host__isys_catg_raid_list__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_raid_list__isys_obj__id
            WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' . $this->convert_sql_id($id) . '
            UNION
            SELECT NULL AS \'objId\', isys_virtual_device_host__cluster_storage AS \'objTitle\', NULL AS \'resourceTitle\'
            FROM isys_virtual_device_host 
            WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' . $this->convert_sql_id($id);
        $resourceData = $this->retrieve($query)->get_row();

        $resourceTitle = '';
        if (!empty($resourceData['resourceTitle'])) {
            $resourceTitle = ' >> ' . $resourceData['resourceTitle'];
        }

        $return = empty($resourceData['objTitle']) ? isys_tenantsettings::get('gui.empty_value', '-') : $resourceData['objTitle'] . $resourceTitle;
        return $return;
    }

    /**
     * Get host network device
     *
     * @param $data
     *
     * @return string
     */
    private function getHostNetworkDevice($id, $type)
    {
        if ($type == defined_or_default('C__NETWORK_TYPE__VIRTUALSWITCH')) {
            // Get switch port group
            $query = 'SELECT isys_virtual_device_host__switch_port_group FROM isys_virtual_device_host WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' .
                $this->convert_sql_id($id);
            $return = "Switch Port Group: " . $this->retrieve($query)->get_row_value('isys_virtual_device_host__switch_port_group');
        } else {
            // Get Port
            $query = 'SELECT isys_obj__id AS \'objId\', isys_obj__title AS \'objTitle\', isys_catg_port_list__title AS \'resourceTitle\' FROM isys_virtual_device_host 
                INNER JOIN isys_catg_port_list ON isys_catg_port_list__id = isys_virtual_device_host__isys_catg_port_list__id
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_port_list__isys_obj__id
                WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' . $this->convert_sql_id($id);
            $resourceData = $this->retrieve($query)->get_row();
            $return = empty($resourceData['objTitle']) ? isys_tenantsettings::get('gui.empty_value', '-') : $resourceData['objTitle'] . ' >> ' .
                $resourceData['resourceTitle'];
        }

        return $return;
    }

    /**
     * Get host interface device
     *
     * @param $data
     *
     * @return string
     */
    private function getHostInterfaceDevice($id)
    {
        $query = 'SELECT isys_obj__id AS \'objId\', isys_obj__title AS \'objTitle\', isys_catg_ui_list__title AS \'resourceTitle\' FROM isys_virtual_device_host 
            INNER JOIN isys_catg_ui_list ON isys_catg_ui_list__id = isys_virtual_device_host__isys_catg_ui_list__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_ui_list__isys_obj__id
            WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = ' . $this->convert_sql_id($id);
        $resourceData = $this->retrieve($query)->get_row();

        $return = empty($resourceData['objTitle']) ? isys_tenantsettings::get('gui.empty_value', '-') : $resourceData['objTitle'] . ' >> ' . $resourceData['resourceTitle'];
        return $return;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function properties()
    {
        return [
            'host_stor_device'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__HOST_RESOURCE',
                        C__PROPERTY__INFO__DESCRIPTION => 'storage devices of host which is defined in category virtual machine'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_host__isys_catg_stor_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_stor_list',
                            'isys_catg_stor_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__HOST_STORAGE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback([
                                    'isys_cmdb_dao_category_g_virtual_devices',
                                    'callback_property_host_stor_device'
                                ])
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'storage_device'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]),
            'host_ldev_client'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_DEVICES__HOST_LDEV_CLIENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'storage devices of host which is defined in category virtual machine'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_host__isys_catg_ldevclient_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_ldevclient_list',
                        'isys_catg_ldevclient_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__HOST_STORAGE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_host_stor_device'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'storage_ldev'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'host_drive'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__HOST_STORAGE',
                    C__PROPERTY__INFO__DESCRIPTION => 'drive of host which is defined in category virtual machine'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_host__isys_catg_drive_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__HOST_STORAGE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_host_stor_device'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'storage_drive'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'host_port'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__HOST_NETWORK_PORT',
                    C__PROPERTY__INFO__DESCRIPTION => 'port of host which is defined in category virtual machine'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_host__isys_catg_port_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_port_list',
                        'isys_catg_port_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__HOST_NETWORK_PORT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_local_host_port'
                        ], ['host'])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'port'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'host_interface'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__HOST_INTERFACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'interface of host which is defined in category virtual machine'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_host__isys_catg_ui_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__HOST_INTERFACE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_host_interface'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'ui',
                        ['host']
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'host_raid'            => new DialogProperty(
                'C__CMDB__CATG__VD__HOST_RAID_ARRAY',
                'LC__CMDB__CATG__VD__HOST_RAID_ARRAY',
                'isys_virtual_device_host__isys_catg_raid_list__id',
                'isys_virtual_device_host',
                'isys_catg_raid_list'
            ),
            'local_stor_device'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_DEVICES__LOCAL_STOR_DEVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'storage devices of host which is defined in category virtual machine'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_local__isys_catg_stor_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__LOCAL_STORAGE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_general'
                        ], ['isys_cmdb_dao_category_g_stor'])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'storage_device'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'local_port'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_DEVICES__LOCAL_PORT',
                    C__PROPERTY__INFO__DESCRIPTION => 'ports of local object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_local__isys_catg_port_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_port_list',
                        'isys_catg_port_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__LOCAL_NETWORK_PORT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_local_host_port'
                        ], ['local'])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'port'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'local_interface'      => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__HOST_RESOURCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'local interfaces from the current object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_device_local__isys_catg_ui_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__LOCAL_INTERFACE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_general'
                        ], ['isys_cmdb_dao_category_g_ui'])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'ui'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'storage_type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_DEVICES__STORAGE_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'storage type of local storage'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_virtual_device_local__isys_virtual_storage_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_virtual_storage_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_virtual_storage_type',
                        'isys_virtual_storage_type__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__STORAGE_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_virtual_storage_type'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'network_type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_DEVICES__NETWORK_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'network type of local network'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_virtual_device_local__isys_virtual_network_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_virtual_network_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_virtual_network_type',
                        'isys_virtual_network_type__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__NETWORK_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_virtual_network_type'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'switch_port_group'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__SWITCH_PORT_GROUP',
                    C__PROPERTY__INFO__DESCRIPTION => 'switch port group from hosts'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_virtual_device_host__switch_port_group',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VD__SWITCH_PORT_GROUP',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_devices',
                            'callback_property_switch_port_group'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'virtual_device_port_group'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'cluster_storage'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_DEVICES__CLUSTER_STORAGE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster storage'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_virtual_device_host__cluster_storage',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_virtual_device_host'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'cluster_interface'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_DEVICES__CLUSTER_INTERFACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster interface'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_virtual_device_host__cluster_ui',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'disk_image_location'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__DISK_IMAGE_LOCATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Local storage device'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_device_list__disk_image_location'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__VD__DISK_IMAGE_LOCATION'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]),
            'device_type'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__DEVICETYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Device type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_device_list__device_type'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VIRTUAL_DEVICES__DEVICE_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => $this->get_device_types()
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'virtual_dev_property_device_type'
                        ]
                    ]
                ]),
            'description'          => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_device_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VIRTUAL_DEVICE', 'C__CATG__VIRTUAL_DEVICE'),
                ],
            ]),
            'virtual_network_type' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VD__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_virtual_network_type__title'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VIRTUAL_DEVICES__VIRTUAL_NETWORK_TYPE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            $l_host_stor_device = $this->get_property('host_stor_device');
            $l_host_ldev_client = $this->get_property('host_ldev_client');
            $l_host_drive = $this->get_property('host_drive');
            $l_cluster_interface = $this->get_property('cluster_interface');
            $l_host_port_device = $this->get_property('host_port');
            $l_host_raid = $this->get_property('host_raid');
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $this->get_property('disk_image_location'),
                        $this->get_property('device_type'),
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('description')
                    ))) {
                        if ($this->get_property('local_stor_device')) {
                            $this->create_local_device(
                                $p_category_data['data_id'],
                                $this->get_property('local_stor_device'),
                                null,
                                null,
                                $this->get_property('storage_type'),
                                null
                            );
                            if ($l_host_stor_device) {
                                $this->create_host_device($p_category_data['data_id'], $l_host_stor_device, null, null, null, "", null, null, null, null);
                            } elseif ($l_host_ldev_client) {
                                $this->create_host_device($p_category_data['data_id'], null, $l_host_ldev_client, null, null, "", null, null, null, null);
                            } elseif ($l_host_drive) {
                                $this->create_host_device($p_category_data['data_id'], null, null, $l_host_drive, null, "", null, null, null, null);
                            } elseif ($l_host_raid) {
                                $this->create_host_device($p_category_data['data_id'], null, null, null, null, "", null, null, null, $l_host_raid);
                            }
                        }
                        if ($this->get_property('local_port')) {
                            $this->create_local_device($p_category_data['data_id'], null, $this->get_property('local_port'), null, null, $this->get_property('network_type'));
                            if ($l_host_port_device) {
                                $this->create_host_device($p_category_data['data_id'], null, null, null, $l_host_port_device['ref_id'], "", null, null, null, null);
                            } elseif (($l_switch_port_group = $this->get_property('switch_port_group'))) {
                                $this->create_host_device($p_category_data['data_id'], null, null, null, null, $l_switch_port_group['vs_port_group_title'], null, null, null, null);
                            }
                        }
                        if ($this->get_property('local_interface')) {
                            $this->create_local_device($p_category_data['data_id'], null, null, $this->get_property('local_interface'), null, null);
                            if ($l_cluster_interface) {
                                $this->create_host_device($p_category_data['data_id'], null, null, null, null, null, null, null, $l_cluster_interface['ref_title'], null);
                            } elseif (($l_host_interface = $this->get_property('host_interface'))) {
                                $this->create_host_device($p_category_data['data_id'], null, null, null, null, null, $l_host_interface, null, null, null);
                            }
                        }
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        $this->get_property('disk_image_location'),
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('description')
                    );
                    if ($this->get_property('local_stor_device')) {
                        $this->save_local_device(
                            null,
                            $this->get_property('local_stor_device'),
                            null,
                            null,
                            $this->get_property('storage_type'),
                            null,
                            $p_category_data['data_id']
                        );
                        $l_ref_id = null;
                        if ($l_host_stor_device) {
                            $this->save_host_device(null, $l_host_stor_device, null, null, null, "", null, null, null, $p_category_data['data_id'], null);
                        } elseif ($l_host_ldev_client) {
                            $this->save_host_device(null, null, $l_host_ldev_client, null, null, "", null, null, null, $p_category_data['data_id'], null);
                        } elseif ($l_host_drive) {
                            $this->save_host_device(null, null, null, $l_host_drive, null, "", null, null, null, $p_category_data['data_id'], null);
                        } elseif ($l_host_raid) {
                            $this->save_host_device(null, null, null, null, null, "", null, null, null, $p_category_data['data_id'], $l_host_raid);
                        }
                    }
                    if ($this->get_property('local_port')) {
                        $this->save_local_device(null, null, $this->get_property('local_port'), null, null, $this->get_property('network_type'), $p_category_data['data_id']);
                        if ($l_host_port_device) {
                            $this->save_host_device(null, null, null, null, $l_host_port_device['ref_id'], null, null, null, null, $p_category_data['data_id'], null);
                        } elseif (($l_switch_port_group = $this->get_property('switch_port_group'))) {
                            $this->save_host_device(null, null, null, null, null, $l_switch_port_group['vs_port_group_title'], null, null, null, $p_category_data['data_id'], null);
                        }
                    }
                    if ($this->get_property('local_interface')) {
                        $this->save_local_device(null, null, null, $this->get_property('local_interface'), null, null, $p_category_data['data_id']);
                        if ($l_cluster_interface) {
                            $this->save_host_device(null, null, null, null, null, null, null, null, $l_cluster_interface['ref_title'], $p_category_data['data_id'], null);
                        } elseif ($l_host_interface = $this->get_property('host_interface')) {
                            $this->save_host_device(null, null, null, null, null, null, $l_host_interface, null, null, $p_category_data['data_id'], null);
                        }
                    }
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     *
     * @param   array $p_catdata
     *
     * @return  integer
     */
    public function determine_host_device_type($p_catdata)
    {
        if ($p_catdata["isys_virtual_device_host__cluster_storage"] || $p_catdata["isys_virtual_device_host__isys_catg_stor_list__id"] ||
            $p_catdata["isys_virtual_device_host__isys_catg_ldevclient_list__id"] || $p_catdata["isys_virtual_device_host__isys_catg_drive_list__id"]) {
            return C__VIRTUAL_DEVICE__STORAGE;
        } elseif ($p_catdata["isys_virtual_device_host__isys_catg_port_list__id"] || $p_catdata["isys_virtual_device_host__switch_port_group"]) {
            return C__VIRTUAL_DEVICE__NETWORK;
        } elseif ($p_catdata["isys_virtual_device_host__cluster_ui"] || $p_catdata["isys_virtual_device_host__isys_catg_ui_list__id"]) {
            return C__VIRTUAL_DEVICE__INTERFACE;
        }

        return C__VIRTUAL_DEVICE__UNASSOCIATED;
    }

    /**
     *
     * @param   array $p_catdata
     *
     * @return  integer
     */
    public function determine_local_device_type($p_catdata = [])
    {
        if ($p_catdata["isys_virtual_device_local__isys_catg_stor_list__id"]) {
            return C__VIRTUAL_DEVICE__STORAGE;
        } elseif ($p_catdata["isys_virtual_device_local__isys_catg_port_list__id"]) {
            return C__VIRTUAL_DEVICE__NETWORK;
        } elseif ($p_catdata["isys_virtual_device_local__isys_catg_ui_list__id"]) {
            return C__VIRTUAL_DEVICE__INTERFACE;
        }

        return C__VIRTUAL_DEVICE__UNASSOCIATED;
    }

    /**
     *
     * @param   integer $p_catg_virtual_device_id
     * @param   integer $p_stor_id
     * @param   integer $p_ldevclient_id
     * @param   integer $p_drive_id
     * @param   integer $p_port_id
     * @param   string  $p_switch_port
     * @param   integer $p_ui_id
     * @param   string  $p_cluster_storage
     * @param   string  $p_cluster_ui
     * @param   integer $p_raid_id
     *
     * @return  mixed
     * @throws  isys_exception_dao
     */
    public function create_host_device(
        $p_catg_virtual_device_id,
        $p_stor_id = null,
        $p_ldevclient_id = null,
        $p_drive_id = null,
        $p_port_id = null,
        $p_switch_port = "",
        $p_ui_id = null,
        $p_cluster_storage = null,
        $p_cluster_ui = null,
        $p_raid_id = null
    ) {
        $l_sql = "INSERT INTO isys_virtual_device_host SET " . "isys_virtual_device_host__isys_catg_virtual_device_list__id = " .
            $this->convert_sql_id($p_catg_virtual_device_id) . ", " . "isys_virtual_device_host__isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_ldevclient_id) .
            ", " . "isys_virtual_device_host__isys_catg_stor_list__id = " . $this->convert_sql_id($p_stor_id) . ", " .
            "isys_virtual_device_host__isys_catg_drive_list__id = " . $this->convert_sql_id($p_drive_id) . ", " . "isys_virtual_device_host__isys_catg_port_list__id = " .
            $this->convert_sql_id($p_port_id) . ", " . "isys_virtual_device_host__switch_port_group = " . $this->convert_sql_text($p_switch_port) . ", " .
            "isys_virtual_device_host__cluster_storage = " . $this->convert_sql_text($p_cluster_storage) . ", " . "isys_virtual_device_host__cluster_ui = " .
            $this->convert_sql_text($p_cluster_ui) . ", " . "isys_virtual_device_host__isys_catg_ui_list__id = " . $this->convert_sql_id($p_ui_id) . ", " .
            "isys_virtual_device_host__isys_catg_raid_list__id = " . $this->convert_sql_id($p_raid_id) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     *
     * @param   integer $p_host_device_id
     * @param   integer $p_stor_id
     * @param   integer $p_ldevclient_id
     * @param   integer $p_drive_id
     * @param   integer $p_port_id
     * @param   string  $p_switch_port
     * @param   integer $p_ui_id
     * @param   string  $p_cluster_storage
     * @param   string  $p_cluster_ui
     * @param   integer $p_category_id
     * @param   integer $p_raid_id
     *
     * @return  integer
     */
    public function save_host_device(
        $p_host_device_id,
        $p_stor_id = null,
        $p_ldevclient_id = null,
        $p_drive_id = null,
        $p_port_id = null,
        $p_switch_port = "",
        $p_ui_id = null,
        $p_cluster_storage = null,
        $p_cluster_ui = null,
        $p_category_id = null,
        $p_raid_id = null
    ) {
        $l_sql = "UPDATE isys_virtual_device_host SET " . "isys_virtual_device_host__isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_ldevclient_id) . ", " .
            "isys_virtual_device_host__isys_catg_stor_list__id = " . $this->convert_sql_id($p_stor_id) . ", " . "isys_virtual_device_host__isys_catg_drive_list__id = " .
            $this->convert_sql_id($p_drive_id) . ", " . "isys_virtual_device_host__isys_catg_port_list__id = " . $this->convert_sql_id($p_port_id) . ", " .
            "isys_virtual_device_host__switch_port_group = " . $this->convert_sql_text($p_switch_port) . ", " . "isys_virtual_device_host__cluster_storage = " .
            $this->convert_sql_text($p_cluster_storage) . ", " . "isys_virtual_device_host__cluster_ui = " . $this->convert_sql_text($p_cluster_ui) . ", " .
            "isys_virtual_device_host__isys_catg_ui_list__id = " . $this->convert_sql_id($p_ui_id) . ", " . "isys_virtual_device_host__isys_catg_raid_list__id = " .
            $this->convert_sql_id($p_raid_id) . " ";

        if ($p_host_device_id > 0) {
            $l_sql .= "WHERE isys_virtual_device_host__id = " . $this->convert_sql_id($p_host_device_id) . ";";
        } elseif ($p_category_id > 0) {
            $l_sql .= "WHERE isys_virtual_device_host__isys_catg_virtual_device_list__id = " . $this->convert_sql_id($p_category_id) . ";";
        } else {
            return false;
        }

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     *
     * @param   integer $p_catg_virtual_device_id
     * @param   integer $p_stor_id
     * @param   integer $p_port_id
     * @param   integer $p_ui_id
     * @param   integer $p_storage_type
     * @param   integer $p_network_type
     *
     * @return  integer
     */
    public function create_local_device($p_catg_virtual_device_id, $p_stor_id = null, $p_port_id = null, $p_ui_id = null, $p_storage_type = null, $p_network_type = null)
    {
        $l_sql = "INSERT INTO isys_virtual_device_local SET " . "isys_virtual_device_local__isys_catg_virtual_device_list__id = " .
            $this->convert_sql_id($p_catg_virtual_device_id) . ", " . "isys_virtual_device_local__isys_catg_stor_list__id = " . $this->convert_sql_id($p_stor_id) . ", " .
            "isys_virtual_device_local__isys_catg_port_list__id = " . $this->convert_sql_id($p_port_id) . ", " . "isys_virtual_device_local__isys_catg_ui_list__id = " .
            $this->convert_sql_id($p_ui_id) . ", " . "isys_virtual_device_local__isys_virtual_storage_type__id = " . $this->convert_sql_id($p_storage_type) . ", " .
            "isys_virtual_device_local__isys_virtual_network_type__id = " . $this->convert_sql_id($p_network_type) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     *
     * @param   integer $p_local_device_id
     * @param   integer $p_stor_id
     * @param   integer $p_port_id
     * @param   integer $p_ui_id
     * @param   integer $p_storage_type
     * @param   integer $p_network_type
     * @param   integer $p_category_id
     *
     * @return  integer
     */
    public function save_local_device(
        $p_local_device_id = null,
        $p_stor_id = null,
        $p_port_id = null,
        $p_ui_id = null,
        $p_storage_type = null,
        $p_network_type = null,
        $p_category_id = null
    ) {
        $l_sql = "UPDATE isys_virtual_device_local SET " . "isys_virtual_device_local__isys_catg_stor_list__id = " . $this->convert_sql_id($p_stor_id) . ", " .
            "isys_virtual_device_local__isys_catg_port_list__id = " . $this->convert_sql_id($p_port_id) . ", " . "isys_virtual_device_local__isys_catg_ui_list__id = " .
            $this->convert_sql_id($p_ui_id) . ", " . "isys_virtual_device_local__isys_virtual_storage_type__id = " . $this->convert_sql_id($p_storage_type) . ", " .
            "isys_virtual_device_local__isys_virtual_network_type__id = " . $this->convert_sql_id($p_network_type) . " ";

        if ($p_local_device_id > 0) {
            $l_sql .= "WHERE isys_virtual_device_local__id = " . $this->convert_sql_id($p_local_device_id) . ";";
        } elseif ($p_category_id > 0) {
            $l_sql .= "WHERE isys_virtual_device_local__isys_catg_virtual_device_list__id = " . $this->convert_sql_id($p_category_id) . ";";
        } else {
            return false;
        }

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Create virtual device.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_disk_image_location
     * @param   integer $p_deviceType
     * @param   integer $p_status
     * @param   string  $p_description
     *
     * @return  integer
     * @throws  isys_exception_dao
     */
    public function create($p_obj_id, $p_disk_image_location = "", $p_deviceType, $p_status = C__RECORD_STATUS__NORMAL, $p_description = "")
    {
        if (!is_numeric($p_status)) {
            $p_status = C__RECORD_STATUS__NORMAL;
        }

        $l_sql = "INSERT INTO isys_catg_virtual_device_list SET " . "isys_catg_virtual_device_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ", " .
            "isys_catg_virtual_device_list__disk_image_location = " . $this->convert_sql_text($p_disk_image_location) . ", " .
            "isys_catg_virtual_device_list__device_type = " . $this->convert_sql_id($p_deviceType) . ", " . "isys_catg_virtual_device_list__status = " .
            $this->convert_sql_id($p_status) . ", " . "isys_catg_virtual_device_list__description = " . $this->convert_sql_text($p_description) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            $this->m_strLogbookSQL = $l_sql;

            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Save virtual device.
     *
     * @param   integer $p_virtual_device_list_id
     * @param   string  $p_disk_image_location
     * @param   integer $p_status
     * @param   string  $p_description
     *
     * @return  integer
     * @throws  isys_exception_dao
     */
    public function save($p_virtual_device_list_id, $p_disk_image_location = "", $p_status = C__RECORD_STATUS__NORMAL, $p_description = "")
    {
        if (!is_numeric($p_status)) {
            $p_status = C__RECORD_STATUS__NORMAL;
        }

        $l_sql = "UPDATE isys_catg_virtual_device_list SET " . "isys_catg_virtual_device_list__disk_image_location = " . $this->convert_sql_text($p_disk_image_location) .
            ", " . "isys_catg_virtual_device_list__status = " . $this->convert_sql_id($p_status) . ", " . "isys_catg_virtual_device_list__description = " .
            $this->convert_sql_text($p_description) . " " . "WHERE isys_catg_virtual_device_list__id = " . $this->convert_sql_id($p_virtual_device_list_id) . ";";

        $this->m_strLogbookSQL = $l_sql;

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        $l_catdata = $this->get_general_data();

        $p_status = $l_catdata["isys_catg_virtual_device_list__status"];
        $l_obj_id = $l_catdata["isys_catg_virtual_device_list__isys_obj__id"];
        $l_status = C__RECORD_STATUS__NORMAL;

        $l_dao_vm = new isys_cmdb_dao_category_g_virtual_machine($this->get_database_component());
        $l_dao_ldev = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());
        $l_dao_ui = new isys_cmdb_dao_category_g_ui($this->get_database_component());

        $l_host_system = $l_dao_vm->get_host_system($l_obj_id);

        // Check if hostsystem is from objecttype cluster
        if ($l_dao_vm->get_objTypeID($l_host_system) === defined_or_default('C__OBJTYPE__CLUSTER')) {
            $l_is_cluster = true;
        } else {
            $l_is_cluster = false;
        }

        $l_host_storage = explode("_", $_POST["C__CMDB__CATG__VD__HOST_STORAGE"]);

        /**
         * Determine host storage selection
         */
        if (count($l_host_storage) == 2) {
            if ($l_host_storage[1] == defined_or_default('C__CATG__STORAGE')) {
                $l_host_stor_id = $l_host_storage[0];
            } elseif ($l_host_storage[1] == defined_or_default('C__CATG__LDEV_CLIENT')) {
                if ($l_is_cluster) {
                    $l_ldev_data = $l_dao_ldev->get_data($l_host_storage[0])
                        ->__to_array();
                    $l_cluster_storage = $l_ldev_data['isys_catg_ldevclient_list__title'];
                } else {
                    $l_host_ldev_client = $l_host_storage[0];
                }
            } elseif ($l_host_storage[1] == defined_or_default('C__CATG__DRIVE')) {
                $l_host_drive = $l_host_storage[0];
            } elseif ($l_host_storage[1] == defined_or_default('C__CATG__RAID')) {
                $l_host_raid = $l_host_storage[0];
            }
        }

        if ($_POST['C__CMDB__CATG__VD__SWITCH_PORT_GROUP'] == "-1") {
            $_POST['C__CMDB__CATG__VD__SWITCH_PORT_GROUP'] = null;
        }

        if (is_numeric($_POST['C__CMDB__CATG__VD__HOST_INTERFACE'])) {
            if ($l_is_cluster) {
                $l_interface_data = $l_dao_ui->get_data($_POST['C__CMDB__CATG__VD__HOST_INTERFACE'])
                    ->__to_array();
                $l_cluster_ui = $l_interface_data['isys_catg_ui_list__title'];
            } else {
                $l_host_ui = $_POST['C__CMDB__CATG__VD__HOST_INTERFACE'];
            }
        }

        $portId = $_POST['C__CMDB__CATG__VD__HOST_NETWORK_PORT'];
        $switchPort = null;

        if ($_POST['C__CMDB__CATG__VD__NETWORK_TYPE'] == defined_or_default('C__NETWORK_TYPE__VIRTUALSWITCH')) {
            $switchPort = html_entity_decode($_POST['C__CMDB__CATG__VD__SWITCH_PORT_GROUP'], null, $GLOBALS['g_config']['html-encoding']);
            $portId = null;
        }

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CMDB__CATG__VD__DISK_IMAGE_LOCATION'],
                $_POST["device_type"],
                $l_status,
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->create_local_device(
                $l_id,
                $_POST['C__CMDB__CATG__VD__LOCAL_STORAGE'],
                $_POST['C__CMDB__CATG__VD__LOCAL_NETWORK_PORT'],
                $_POST['C__CMDB__CATG__VD__LOCAL_INTERFACE'],
                $_POST['C__CMDB__CATG__VD__STORAGE_TYPE'],
                $_POST['C__CMDB__CATG__VD__NETWORK_TYPE']
            );

            $this->create_host_device(
                $l_id,
                $l_host_stor_id,
                $l_host_ldev_client,
                $l_host_drive,
                $portId,
                $switchPort,
                $l_host_ui,
                $l_cluster_storage,
                $l_cluster_ui,
                null,
                $l_host_raid
            );

            if ($l_id > 0) {
                $p_cat_level = null;

                return $l_id;
            }
        } else {
            $this->save_local_device(
                $l_catdata["isys_virtual_device_local__id"],
                $_POST['C__CMDB__CATG__VD__LOCAL_STORAGE'],
                $_POST['C__CMDB__CATG__VD__LOCAL_NETWORK_PORT'],
                $_POST['C__CMDB__CATG__VD__LOCAL_INTERFACE'],
                $_POST['C__CMDB__CATG__VD__STORAGE_TYPE'],
                $_POST['C__CMDB__CATG__VD__NETWORK_TYPE']
            );

            $this->save_host_device(
                $l_catdata["isys_virtual_device_host__id"],
                $l_host_stor_id,
                $l_host_ldev_client,
                $l_host_drive,
                $portId,
                $switchPort,
                $l_host_ui,
                $l_cluster_storage,
                $l_cluster_ui,
                null,
                $l_host_raid
            );

            return $this->save(
                $l_catdata["isys_catg_virtual_device_list__id"],
                $_POST['C__CMDB__CATG__VD__DISK_IMAGE_LOCATION'],
                $l_status,
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
        }

        return false;
    }

    /**
     * Helper method to get the content for the dialog list assigned storage devices
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    private function get_dialog_stor_content_by_host($p_obj_id)
    {
        $l_dao_stor = new isys_cmdb_dao_category_g_stor($this->get_database_component());
        $l_dao_ldev = new isys_cmdb_dao_category_g_ldevclient($this->get_database_component());
        $l_dao_driv = new isys_cmdb_dao_category_g_drive($this->get_database_component());

        $l_res = $l_dao_stor->get_data(null, $p_obj_id);
        while ($l_row = $l_res->get_row()) {
            $l_id = $l_row["isys_catg_stor_list__id"] . "," . defined_or_default('C__CATG__STORAGE');
            $l_host_storage[isys_application::instance()->container->get('language')
                ->get('LC__STORAGE_DEVICE')][$l_id] = $l_row["isys_catg_stor_list__title"] . " (" . isys_application::instance()->container->get('language')
                    ->get($l_row["isys_stor_manufacturer__title"]) . ")";
        }

        $l_res = $l_dao_ldev->get_data(null, $p_obj_id);
        while ($l_row = $l_res->get_row()) {
            $l_id = $l_row["isys_catg_ldevclient_list__id"] . "," . defined_or_default('C__CATG__LDEV_CLIENT');
            $l_host_storage[isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__LDEV_CLIENT')][$l_id] = $l_row["isys_catg_ldevclient_list__title"] . " (" . isys_application::instance()->container->get('language')
                    ->get($l_row["isys_catg_sanpool_list__title"]) . ")";
        }

        $l_res = $l_dao_driv->get_data(null, $p_obj_id);
        while ($l_row = $l_res->get_row()) {
            $l_id = $l_row["isys_catg_drive_list__id"] . "," . defined_or_default('C__CATG__DRIVE');
            $l_host_storage[isys_application::instance()->container->get('language')
                ->get('LC__STORAGE_DRIVE')][$l_id] = $l_row["isys_catg_drive_list__title"] . " (" . isys_application::instance()->container->get('language')
                    ->get($l_row["isys_catg_drive_list__driveletter"]) . ")";
        }

        return $l_host_storage;
    }

    /**
     * @param int $p_object_id
     *
     * @return array
     */
    public function get_raid_list($p_object_id)
    {
        $return = [];
        /**
         * @var $raid_dao isys_cmdb_dao_category_g_raid
         */
        $raid_dao = isys_cmdb_dao_category_g_raid::instance(isys_application::instance()->container->get('database'));
        $raids = $raid_dao->get_raids(null, null, $p_object_id);
        while ($l_row = $raids->get_row()) {
            $l_id = $l_row['isys_catg_raid_list__id'] . '_' . defined_or_default('C__CATG__RAID', 'C__CATG__RAID');
            $return[$l_id] = $l_row['isys_catg_raid_list__title'];
        }
        return $return;
    }
}
