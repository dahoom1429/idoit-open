<?php

/**
 * i-doit
 *
 * JDisc device DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_jdisc_dao_devices extends isys_jdisc_dao_data
{
    /**
     * Cache profile assignments
     *
     * @var array
     */
    protected $m_assignments = [];

    /**
     * Cached information about connections between blade and devices
     *
     * @var array
     */
    protected $m_cached_blade_connections = [];

    /**
     * @var isys_array
     */
    protected $m_cached_management_device_connection = null;

    /**
     * Cached information about virtual hosts
     *
     * @var array Associative array
     *
     * @see prepare_virtual_machine()
     */
    protected $m_cached_virtual_hosts = [];

    /**
     * Condition statement
     *
     * @var string
     */
    protected $m_device_filter_condition = '';

    /**
     * Join statement
     *
     * @var string
     */
    protected $m_device_filter_join = '';

    /**
     * Caches model dialog
     *
     * @var isys_array
     */
    protected $m_model_dialog_cache = null;

    /**
     * Contains all devices which contains modules
     *
     * @var isys_array
     */
    protected $m_module_slots = null;

    /**
     * @var isys_array
     */
    protected $m_object_type_categories = null;

    /**
     * @var isys_array
     */
    private $m_cached_object_types = null;

    /**
     * @var int
     */
    protected $m_module_objecttype;

    /**
     * @var int
     */
    protected $moduleUpdateObjtype = false;

    /**
     * Flag which determines if object type should be updated or not
     *
     * @var bool
     */
    protected $m_update_objtype = true;

    /**
     * Flag which determines if object title should be updated or not
     *
     * @var bool
     */
    protected $m_update_obj_title = false;

    /**
     * Map cmdb-status
     *
     * @var null
     */
    protected $m_cmdb_status_map = null;

    /**
     * Default cmdb status if set
     *
     * @var null
     */
    protected $m_default_cmdb_status = null;

    /**
     * @return boolean
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_update_objtype()
    {
        return $this->m_update_objtype;
    }

    /**
     * @param boolean $m_update_objtype
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function set_update_objtype($m_update_objtype)
    {
        $this->m_update_objtype = $m_update_objtype;
    }

    /**
     * @return bool
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_update_obj_title()
    {
        return $this->m_update_obj_title;
    }

    /**
     * @param bool $m_update_obj_title
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function set_update_obj_title($p_value)
    {
        $this->m_update_obj_title = $p_value;
    }

    /**
     * Map cmdb status
     */
    private function map_cmdb_status($p_default_cmdb_status = null)
    {
        global $g_comp_database;

        if ($p_default_cmdb_status !== null) {
            $this->m_default_cmdb_status = $p_default_cmdb_status;
        }

        /**
         * @var $l_dao isys_cmdb_dao_status
         */
        $l_dao = isys_cmdb_dao_status::instance($g_comp_database);
        $l_res = $l_dao->get_cmdb_status();
        while ($l_row = $l_res->get_row()) {
            $this->m_cmdb_status_map[$l_row['isys_cmdb_status__id']] = [
                'tag'        => 'cmdb_status',
                'value'      => $this->language->get($l_row['isys_cmdb_status__title']),
                'id'         => $l_row['isys_cmdb_status__id'],
                'const'      => $l_row['isys_cmdb_status__const'],
                'title_lang' => $l_row['isys_cmdb_status__title'],
                'title'      => 'LC__UNIVERSAL__CMDB_STATUS'
            ];
        }
    }

    /**
     * Get default cmdb-status
     *
     * @return null
     */
    public function get_default_cmdb_status()
    {
        return $this->m_default_cmdb_status;
    }

    /**
     * Prepares device environment for:
     * - Used CMDB status while importing
     * - Update object type while importing
     * - Update object title while importing
     *
     * @param array $p_cached_profile
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function prepare_device_environment(array $p_cached_profile)
    {
        // Map cmdb status and set default cmdb status if its set
        $this->map_cmdb_status($p_cached_profile['cmdb_status']);
        // Set config if object type should be updated or not
        $this->set_update_objtype((bool)$p_cached_profile['update_objtype']);
        // Set config if object title should be updated or not
        $this->set_update_obj_title((bool)$p_cached_profile['update_obj_title']);
    }

    /**
     * Sets object type for module inside chassis devices
     *
     * @param $p_objecttype
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_module_objecttype($p_objecttype)
    {
        $this->m_module_objecttype = $p_objecttype;
    }

    /**
     * @param boolean $value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setModuleUpdateObjtype($value = false)
    {
        $this->moduleUpdateObjtype = $value;
    }

    /**
     * Sets device filter
     *
     * @param $p_query_join
     */
    public function set_device_filter_join($p_query_join)
    {
        $this->m_device_filter_join = $p_query_join;
    }

    /**
     * Sets device filter condition
     *
     * @param $p_query_condition
     */
    public function set_device_filter_condition($p_query_condition)
    {
        $this->m_device_filter_condition = $p_query_condition;
    }

    /**
     * Method for receiving all devices of a given group.
     *
     * @param   integer $p_group
     * @param   mixed   $p_id May be integer or array of integers.
     *
     * @return  resource
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_devices($p_group = null, $p_id = null)
    {
        $l_sql = 'SELECT d.* FROM device AS d
			LEFT JOIN devicegroupdevicerelation AS dg
			ON dg.deviceid = d.id
			WHERE TRUE ';

        if ($p_group !== null) {
            $l_sql .= 'AND dg.devicegroupid = ' . $this->convert_sql_id($p_group) . ' ';
        }

        if ($p_id !== null) {
            if (is_array($p_id) && count($p_id) > 0) {
                $l_sql .= 'AND d.id IN (' . implode(', ', $p_id) . ') ';
            } elseif ($p_id > 0) {
                $l_sql .= 'AND d.id = ' . $this->convert_sql_id($p_id) . ' ';
            }
        }

        $l_sql .= 'ORDER BY d.id;';

        return $this->fetch($l_sql);
    }

    /**
     * Method for receiving all videocontrollers of a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_videocontroller_by_device($p_id, $p_raw = false)
    {
        $l_return = [];
        $l_sql = 'SELECT v.* FROM videocontroller AS v
			LEFT JOIN videocontrollerslot AS vs
			ON vs.itemid = v.id
			WHERE vs.deviceid = ' . $this->convert_sql_id($p_id) . ';';

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                $l_return[] = $this->prepare_videocontroller($l_row);
            }
        }

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get('LC__CMDB__CATG__GRAPHIC'),
                'const'             => 'C__CATG__GRAPHIC',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for receiving all processors of a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_processor_by_device($p_id, $p_raw = false)
    {
        $l_return = [];
        $l_sql = 'SELECT p.* FROM processor AS p
			LEFT JOIN processorslot AS ps
			ON ps.itemid = p.id
			WHERE ps.deviceid = ' . $this->convert_sql_id($p_id) . ';';

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                $l_return[] = $this->prepare_processor($l_row);
            }
        }

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get('LC__CMDB__CATG__CPU'),
                'const'             => 'C__CATG__CPU',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for receiving all memory-entries of a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_memory_by_device($p_id, $p_raw = false)
    {
        $l_return = [];
        $l_sql = 'SELECT m.*, ms.socketdesignation FROM memorymodule AS m
			LEFT JOIN memorymoduleslot AS ms
			ON ms.itemid = m.id
			WHERE ms.deviceid = ' . $this->convert_sql_id($p_id) . ';';

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                $l_return[] = $this->prepare_memory($l_row);
            }
        }

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get('LC__CMDB__CATG__MEMORY'),
                'const'             => 'C__CATG__MEMORY',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for receiving all physical discs of a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_physicaldisk_by_device($p_id, $p_raw = false)
    {
        $l_return = [];
        $l_sql = 'SELECT pd.* FROM physicaldisk AS pd
			LEFT JOIN physicaldiskslot AS pds
			ON pds.itemid = pd.id
			WHERE pds.deviceid = ' . $this->convert_sql_id($p_id) . ';';

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                $l_return[] = $this->prepare_storage($l_row);
            }
        }

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get('LC__STORAGE_DEVICE'),
                'const'             => 'C__CATG__STORAGE_DEVICE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for receiving all logical discs of a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_logicaldisk_by_device($p_id, $p_raw = false)
    {
        $l_return = [];
        $l_sql = 'SELECT ld.* FROM logicaldisk AS ld
			WHERE ld.deviceid = ' . $this->convert_sql_id($p_id) . ';';

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                $l_return[] = $this->prepare_drive($l_row);
            }
        }

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get('LC__STORAGE_DRIVE'),
                'const'             => 'C__CATG__DRIVE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array   $p_data
     * @param   integer $p_mode
     * @param   integer $p_object_id
     *
     * @return  array|bool
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_device_array(array $p_data, $p_mode, $p_object_id = null)
    {
        // If we don´t have the object type than we cannot determine in which object type this device should be imported
        if (isset($p_data['idoit_obj_type'])) {
            // We get an instance of isys_cmdb_dao_jdisc for two small calls.
            $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
            $cmdbStatusDao = isys_cmdb_dao_status::instance($this->m_db);

            // We retrieve the object-type and group info.
            if (!$this->m_cached_object_types) {
                $this->m_cached_object_types = new isys_array();
            }
            if (!isset($this->m_cached_object_types[$p_data['idoit_obj_type']])) {
                $this->m_cached_object_types[$p_data['idoit_obj_type']] = $l_dao->get_objtype($p_data['idoit_obj_type'], true)
                    ->get_row();
            }

            $l_default_cmdb_status = $this->get_default_cmdb_status();
            $l_cmdb_status = defined_or_default('C__CMDB_STATUS__IN_OPERATION');
            $l_change_cmdb_status = false;
            if ($l_default_cmdb_status !== null) {
                $l_change_cmdb_status = true;
                $l_cmdb_status = $l_default_cmdb_status;
            }

            $l_obj_type = [
                'value'        => $this->language->get($this->m_cached_object_types[$p_data['idoit_obj_type']]['isys_obj_type__title']),
                'id'           => $this->m_cached_object_types[$p_data['idoit_obj_type']]['isys_obj_type__id'],
                'const'        => $this->m_cached_object_types[$p_data['idoit_obj_type']]['isys_obj_type__const'],
                'title_lang'   => $this->m_cached_object_types[$p_data['idoit_obj_type']]['isys_obj_type__title'],
                'group'        => $this->m_cached_object_types[$p_data['idoit_obj_type']]['isys_obj_type_group__const'],
                'sysid_prefix' => $this->m_cached_object_types[$p_data['idoit_obj_type']]['isys_obj_type__sysid_prefix'],
            ];
            $l_return = [
                C__DATA__VALUE => $p_data['name'],
                C__DATA__TITLE => $p_data['name'],
                'type'         => $l_obj_type,
                'created'      => strtotime($p_data['creationtime']),
                'updated'      => time(),
                // now
                'status'       => C__RECORD_STATUS__NORMAL,
                'cmdb_status'  => $l_cmdb_status,
                'categories'   => []
            ];

            $this->m_log->debug('> Preparing object title "' . $p_data['name'] . '"');
            $this->m_log->debug('> Preparing object type "' . $l_obj_type['value'] . '"');
            if ($p_mode != isys_import_handler_cmdb::C__APPEND && $p_object_id !== null) {
                $l_object_info = $l_dao->get_object_by_id($p_object_id)
                    ->get_row();
                if ($l_object_info) {
                    $this->m_log->debug('> Object data shall be updated - Object #' . $l_object_info['isys_obj__id']);
                    $l_return['id'] = $l_object_info['isys_obj__id'];
                    $l_return['sysid'] = $l_object_info['isys_obj__sysid'];
                    $l_return['created_by'] = $l_object_info['isys_obj__created_by'];
                    $l_return['created'] = strtotime($l_object_info['isys_obj__created']);
                    $l_return['cmdb_status'] = ($l_change_cmdb_status) ? $l_cmdb_status : $l_object_info['isys_obj__isys_cmdb_status__id'];
                    $l_return['status'] = $l_object_info['isys_obj__status'];
                    $l_return['description'] = $l_object_info['isys_obj__description'];
                    $l_changes = [];
                    // In Case we found the object but the object type is different, we update the object type of the found object
                    if ($this->get_update_objtype() === true && (int) $l_object_info['isys_obj_type__id'] != (int) $p_data['idoit_obj_type']) {
                        $this->m_log->debug('> Object type is different. Changing from "' . $this->language->get($l_object_info['isys_obj_type__title']) . '" to "' . $l_obj_type['value'] . '".');
                        $l_dao->update('UPDATE isys_obj SET isys_obj__updated = NOW(), isys_obj__isys_obj_type__id = \'' . $p_data['idoit_obj_type'] .
                            '\' WHERE isys_obj__id = \'' . $p_object_id . '\'');

                        $l_changes['isys_cmdb_dao_category_g_global::type'] = [
                            'from' => $this->language->get($l_object_info['isys_obj_type__title']),
                            'to'   => $l_obj_type['value']
                        ];
                    } else {
                        $this->m_log->debug('Updating object type is deactivated.');
                    }

                    if ($this->get_update_obj_title() === true && $l_object_info['isys_obj__title'] != $p_data['name']) {
                        $l_dao->update('UPDATE isys_obj SET isys_obj__title = ' . $this->convert_sql_text($p_data['name']) . ' WHERE isys_obj__id = ' .
                            $this->convert_sql_id($p_object_id));
                        $l_changes['isys_cmdb_dao_category_g_global::title'] = [
                            'from' => $l_object_info['isys_obj__title'],
                            'to'   => $p_data['name']
                        ];
                    }

                    if ($l_change_cmdb_status !== false && (int)$l_cmdb_status !== (int)$l_object_info['isys_obj__isys_cmdb_status__id']) {
                        $newStatus = $cmdbStatusDao->get_cmdb_status($l_cmdb_status)->get_row();
                        $oldStatus = $cmdbStatusDao->get_cmdb_status($l_object_info['isys_obj__isys_cmdb_status__id'])->get_row();

                        $l_changes['isys_cmdb_dao_category_g_global::cmdb_status'] = [
                            'from' => $this->language->get($oldStatus['isys_cmdb_status__title']),
                            'to' => $this->language->get($newStatus['isys_cmdb_status__title'])
                        ];
                    }

                    if (($l_changes_count = count($l_changes))) {
                        $l_serialized_changes = serialize($l_changes);

                        self::set_logbook_entries([
                            'object_id'      => $p_object_id,
                            'object_type_id' => $p_data['idoit_obj_type'],
                            'category'       => 'LC__CMDB__CATG__GLOBAL',
                            'changes'        => $l_serialized_changes,
                            'count_changes'  => $l_changes_count
                        ]);
                    }
                }
            }

            return $l_return;
        } else {
            return false;
        }
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_videocontroller(array $p_data)
    {
        //$this->m_log->debug('>> Preparing videocontroller array');

        $l_memory_data = $this->convert_memory_for_import($p_data['installedramkb']);
        $p_data['installedramkb'] = $l_memory_data[0];
        $l_unit = $l_memory_data[1];
        $l_unit['tag'] = 'unit';

        return [
            'data_id'    => null,
            'properties' => [
                'title'        => [
                    'tag'   => 'title',
                    'value' => $p_data['model'],
                    'title' => 'LC__CMDB__CATG__TITLE',
                ],
                'manufacturer' => [
                    'tag'        => 'manufacturer',
                    'value'      => $p_data['manufacturer'],
                    'title_lang' => $p_data['manufacturer'],
                    'title'      => 'LC__CMDB__CATG__MANUFACTURE',
                ],
                'memory'       => [
                    'tag'   => 'memory',
                    'value' => $p_data['installedramkb'],
                    'title' => 'LC__CMDB__CATG__MEMORY',
                ],
                'unit'         => $l_unit
            ]
        ];
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_processor(array $p_data)
    {
        //$this->m_log->debug('>> Preparing CPU array');

        $l_frequency = (int)(($p_data['maxclockspeed'] > 0) ? $p_data['maxclockspeed'] : $p_data['currentclockspeed']);

        return [
            'data_id'    => null,
            'properties' => [
                'title'          => [
                    'tag'   => 'title',
                    'value' => $p_data['model'],
                    'title' => 'LC__CMDB__CATG__CPU_TITLE',
                ],
                'manufacturer'   => [
                    'tag'        => 'manufacturer',
                    'value'      => $p_data['manufacturer'],
                    'title_lang' => $p_data['manufacturer'],
                    'title'      => 'LC__CATG__STORAGE_CONTROLLER_MANUFACTURER',
                ],
                'type'           => [
                    'tag'        => 'type',
                    'value'      => $p_data['model'],
                    'title_lang' => $p_data['model'],
                    'title'      => 'LC__CMDB__CATG__CPU_TYPE',
                ],
                'frequency'      => [
                    'tag'   => 'frequency',
                    'value' => $l_frequency,
                    'title' => 'LC__CMDB__CATG__FREQUENCY',
                ],
                'cores'          => [
                    'tag'   => 'cores',
                    'value' => $p_data['numberofcores'],
                    'title' => 'LC__CMDB__CATG__CPU_CORES'
                ],
                'frequency_unit' => [
                    'tag'        => 'frequency_unit',
                    'value'      => 'MHz',
                    'id'         => defined_or_default('C__FREQUENCY_UNIT__MHZ', 2),
                    'const'      => 'C__FREQUENCY_UNIT__MHZ',
                    'title_lang' => 'MHz',
                    'title'      => 'LC__CMDB__CATG__CPU_FREQUENCY_UNIT',
                ]
            ]
        ];
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_memory(array $p_data)
    {
        //$this->m_log->debug('>> Preparing memory (RAM) array');

        $l_unit = [
            'tag'        => 'unit',
            'value'      => 'MB',
            'id'         => defined_or_default('C__MEMORY_UNIT__MB', 2),
            'const'      => 'C__MEMORY_UNIT__MB',
            'title_lang' => 'MB',
            'title'      => 'LC__CATG__MEMORY_UNIT',
        ];

        if ($p_data['size'] >= 1024) {
            // Convert into Byte
            $l_byte = isys_convert::memory(($p_data['size']), "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__FORMWARD);
            $p_data['size'] = isys_convert::memory($l_byte, 'C__MEMORY_UNIT__GB', C__CONVERT_DIRECTION__BACKWARD);
            $l_unit = [
                'tag'        => 'unit',
                'value'      => 'GB',
                'id'         => defined_or_default('C__MEMORY_UNIT__GB', 3),
                'const'      => 'C__MEMORY_UNIT__GB',
                'title_lang' => 'GB',
                'title'      => 'LC__CATG__MEMORY_UNIT',
            ];
        }

        return [
            'data_id'    => null,
            'properties' => [
                'manufacturer' => [
                    'tag'        => 'manufacturer',
                    'value'      => $p_data['manufacturer'],
                    'title_lang' => $p_data['manufacturer'],
                    'title'      => 'LC__CMDB_CATG__MEMORY_MANUFACTURER',
                ],
                'title'        => [
                    'tag'        => 'title',
                    'value'      => (empty($p_data['model'])) ? $p_data['socketdesignation'] : $p_data['model'],
                    'title_lang' => $p_data['socketdesignation'],
                    'title'      => 'LC__CMDB__CATG__TITLE',
                ],
                'type'         => [
                    'tag'        => 'type',
                    'value'      => $p_data['model'],
                    'title_lang' => $p_data['model'],
                    'title'      => 'LC__CMDB_CATG__MEMORY_TYPE',
                ],
                'capacity'     => [
                    'tag'   => 'capacity',
                    'value' => $p_data['size'],
                    'title' => 'LC__CMDB__CATS__SAN_CAPACITY_VALUE',
                ],
                'unit'         => $l_unit
            ]
        ];
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_storage(array $p_data)
    {
        //$this->m_log->debug('>> Preparing storage (HDD) array');

        $l_memory_data = $this->convert_memory_for_import($p_data['size']);
        $p_data['size'] = $l_memory_data[0];
        $l_unit = $l_memory_data[1];
        $l_unit['tag'] = 'unit';

        // @todo Create logik to differ between object-types which have HDD's and other storage types (Tape, ...).
        return [
            'data_id'    => null,
            'properties' => [
                'title'        => [
                    'tag'   => 'title',
                    'value' => $p_data['model'],
                    'title' => 'LC__CMDB__LOGBOOK__TITLE',
                ],
                'manufacturer' => [
                    'tag'        => 'manufacturer',
                    'value'      => $p_data['manufacturer'],
                    'title_lang' => $p_data['manufacturer'],
                    'title'      => 'LC__CMDB__CATG__MANUFACTURE',
                ],
                'type'         => [
                    'tag'        => 'type',
                    'value'      => $this->language->get('LC__STORAGE_TYPE__HARD_DISK'),
                    'id'         => defined_or_default('C__STOR_TYPE_DEVICE_HD', 1),
                    'const'      => 'C__STOR_TYPE_DEVICE_HD',
                    'title_lang' => 'LC__STORAGE_TYPE__HARD_DISK',
                    'title'      => 'LC__CMDB__CATG__TYPE',
                ],
                'model'        => [
                    'tag'        => 'model',
                    'value'      => $p_data['model'],
                    'title_lang' => $p_data['model'],
                    'title'      => 'LC__CATG__STORAGE_MODEL',
                ],
                'serial'       => [
                    'tag'   => 'serial',
                    'value' => $p_data['serialnumber'],
                    'title' => 'LC__CATG__STORAGE_SERIAL'
                ],
                'unit'         => $l_unit,
                'capacity'     => [
                    'tag'   => 'capacity',
                    'value' => $p_data['size'],
                    'title' => 'LC__CMDB_CATG__MEMORY_CAPACITY',
                ]
            ]
        ];
    }

    /**
     * Helper method which converts and returns the size and unit
     *
     * @param $p_size
     *
     * @return SplFixedArray
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function convert_memory_for_import($p_size)
    {
        $l_return = new SplFixedArray(2);
        $l_unit = [
            'value'      => 'KB',
            'id'         => defined_or_default('C__MEMORY_UNIT__KB', 1),
            'const'      => 'C__MEMORY_UNIT__KB',
            'title_lang' => 'KB',
            'title'      => 'LC__CATG__MEMORY_UNIT',
        ];

        if ($p_size >= 1024) {
            $l_byte = isys_convert::memory(($p_size), "C__MEMORY_UNIT__KB", C__CONVERT_DIRECTION__FORMWARD);

            // MB
            $p_size = isys_convert::memory($l_byte, "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD);
            $l_unit['value'] = 'MB';
            $l_unit['id'] = defined_or_default('C__MEMORY_UNIT__MB', 2);
            $l_unit['const'] = 'C__MEMORY_UNIT__MB';
            $l_unit['title_lang'] = 'MB';

            if (($l_byte / 1024 / 1024) >= 1024) {
                // GB
                $p_size = isys_convert::memory($l_byte, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                $l_unit['value'] = 'GB';
                $l_unit['id'] = defined_or_default('C__MEMORY_UNIT__GB', 3);
                $l_unit['const'] = 'C__MEMORY_UNIT__GB';
                $l_unit['title_lang'] = 'GB';

                if (($l_byte / 1024 / 1024 / 1024) >= 1024) {
                    // GB
                    $p_size = isys_convert::memory($l_byte, "C__MEMORY_UNIT__TB", C__CONVERT_DIRECTION__BACKWARD);
                    $l_unit['value'] = 'TB';
                    $l_unit['id'] = defined_or_default('C__MEMORY_UNIT__TB', 4);
                    $l_unit['const'] = 'C__MEMORY_UNIT__TB';
                    $l_unit['title_lang'] = 'TB';
                }
            }
        }
        $l_return[0] = $p_size;
        $l_return[1] = $l_unit;

        return $l_return;
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_drive(array $p_data)
    {
        //$this->m_log->debug('>> Preparing drive (HDD, CD, DVD, ...) array');

        $l_totalsize_data = $this->convert_memory_for_import($p_data['totalsize']);
        $p_data['totalsize'] = $l_totalsize_data[0];
        $l_unit = $l_totalsize_data[1];
        $l_unit['tag'] = 'unit';

        $l_free_space_data = $this->convert_memory_for_import($p_data['freespace']);
        $p_data['freespace'] = $l_free_space_data[0];
        $l_free_space_unit = $l_free_space_data[1];
        $l_free_space_unit['tag'] = 'free_space_unit';

        $l_used_space_data = $this->convert_memory_for_import($p_data['usedspace']);
        $p_data['usedspace'] = $l_used_space_data[0];
        $l_used_space_unit = $l_used_space_data[1];
        $l_used_space_unit['tag'] = 'used_space_unit';

        return [
            'data_id'    => null,
            'properties' => [
                'mount_point'     => [
                    'tag'   => 'mount_point',
                    'value' => $p_data['mountpoint'],
                    'title' => 'LC__CMDB__CATG__DRIVE_DRIVELETTER'
                ],
                'title'           => [
                    'tag'   => 'title',
                    'value' => $p_data['name'],
                    'title' => 'LC__CMDB__CATG__TITLE'
                ],
                'system_drive'    => [
                    'tag'   => 'system_drive',
                    'value' => (substr($p_data['mountpoint'], 0, 4) == '\\\\?\\' ? '1' : '0'),
                    'title' => 'LC__CMDB__CATG__DRIVE__SYSTEM_DRIVE'
                ],
                'filesystem'      => [
                    'tag'        => 'filesystem',
                    'value'      => $p_data['fstype'],
                    'title_lang' => $p_data['fstype'],
                    'title'      => 'isys_filesystem_type'
                ],
                'unit'            => $l_unit,
                'capacity'        => [
                    'tag'   => 'capacity',
                    'value' => $p_data['totalsize'],
                    'title' => 'LC__CMDB_CATG__MEMORY_CAPACITY'
                ],
                'drive_type'      => [
                    'tag'   => 'drive_type',
                    'value' => 1,
                    // This seems to be "1". Always.
                    'title' => 'LC__CMDB__CATG__TYPE'
                ],
                'serial'          => [
                    'tag'   => 'serial',
                    'value' => $p_data['serialnumber'],
                    'title' => 'LC__CMDB__CATG__SERIAL'
                ],
                'free_space'      => [
                    'tag'   => 'free_space',
                    'value' => $p_data['freespace'],
                    'title' => 'LC__CMDB__CATG__DRIVE__FREE_SPACE'
                ],
                'free_space_unit' => $l_free_space_unit,
                'used_space'      => [
                    'tag'   => 'used_space',
                    'value' => $p_data['usedspace'],
                    'title' => 'LC__CMDB__CATG__DRIVE__USED_SPACE'
                ],
                'used_space_unit' => $l_used_space_unit
            ]
        ];
    }

    /**
     * Fetches all operating systems from database. This data depends on the
     * JDisc release.
     *
     * @return array
     */
    public function get_operating_systems()
    {
        $l_query = 'SELECT * FROM operatingsystem;';

        return $this->fetch_array($l_query);
    }

    /**
     * Gets all connections between virtual machine to host
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_virtual_machine_connections($p_to_id = null, $p_from_id = null, $p_as_result_set = false)
    {
        // FROM VIEW virtualcomputersdevicesview in jdisc
        $l_sql = /** @lang PostgreSQL */
            'SELECT ddc.fromdeviceid AS fromid, ddc.todeviceid AS toid, d2.name, d2.type, d2.serialnumber, t4.address AS ip4address
			FROM devicedeviceconnection ddc, device d2
			LEFT JOIN ip4transport t4 ON d2.id = t4.deviceid AND t4.isdiscoverytransport = TRUE
			WHERE d2.id = ddc.todeviceid AND (ddc.connectortype = ANY (ARRAY[10000, 10001, 10002, 10003, 10004, 10005, 10006, 10007, 10008, 10009, 10010]))';

        if ($p_to_id !== null) {
            $l_sql .= ' AND d2.id = ' . $this->convert_sql_id($p_to_id) . ' ';
        }
        if ($p_from_id !== null) {
            $l_sql .= ' AND ddc.fromdeviceid = ' . $this->convert_sql_id($p_from_id) . ' ';
        }

        $l_res = $this->fetch($l_sql);

        if ($p_as_result_set) {
            return $l_res;
        } else {
            $l_result = [];
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_result[$l_row['toid']] = $l_row['fromid'];
            }

            return $l_result;
        }
    }

    /**
     * Prepares the assigned host for the virtual machine
     *
     * @param       $p_id
     * @param       $p_jdisc_to_idoit
     * @param array $p_object_ids
     *
     * @author      Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_vm_host_by_device($p_id, &$p_jdisc_to_idoit, &$p_object_ids = [])
    {
        /**
         * IDE typehinting helper.
         *
         * @var  $l_dao                isys_cmdb_dao_jdisc
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        $l_conditions_cache = $this->m_device_filter_condition;
        $l_join_cache = $this->m_device_filter_join;
        unset($this->m_device_filter_condition);
        unset($this->m_device_filter_join);
        $l_res = $this->get_devices_by_profile(null, (int)$p_id, null, true);
        $this->m_device_filter_condition = $l_conditions_cache;
        $this->m_device_filter_join = $l_join_cache;

        //		$this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' cluster rows');
        if ($this->m_pdo->num_rows($l_res) > 0) {
            $l_row = $this->m_pdo->fetch_row_assoc($l_res);

            // Check if jdisc id exists in p_jdisc_to_idoit or in category identifer
            if (isset($p_jdisc_to_idoit[$p_id])) {
                $l_object_id = $p_jdisc_to_idoit[$p_id];
                $p_object_ids[$l_object_id] = $p_jdisc_to_idoit[$p_id];
            } else {
                $l_object_id = isys_jdisc_dao_matching::instance()
                    ->get_object_id_by_device_id($l_row['id']);

                // second attempt
                if (!$l_object_id) {
                    $l_object_id = $l_dao->get_obj_id_by_title($l_row['name'], $l_row['idoit_obj_type']);
                    // Second attempt get Object ID by Serial and mac addresses
                    if ($l_object_id === false) {
                        $this->m_log->warning('Virtual host with device id "' . $p_id . '" not found.');
                    }
                }

                if ($l_object_id) {
                    $this->m_log->debug('Virtual Host with device id "' . $p_id . '" found in the system with ObjectID "' . $l_object_id . '".');
                    $p_jdisc_to_idoit[$p_id] = $l_object_id;
                    // Remember Host object id for the import
                    $p_object_ids[$l_object_id] = $l_object_id;
                    if (in_array($l_object_id, isys_cmdb_dao_category_g_identifier::get_cached_objects()) &&
                        isys_cmdb_dao_category_g_identifier::is_identifier_missing($l_object_id)) {
                        /**
                         * Cache device id with object id and create identifier
                         */
                        isys_cmdb_dao_category_g_identifier::set_object_id_by_identifier($l_object_id, $l_row['id']);
                        $l_arr = [
                            'value'        => $l_row['id'],
                            'last_scan'    => $l_row['discoverytime'],
                            'group'        => $l_row['group_name'],
                            'isys_obj__id' => $l_object_id,
                            'description'  => 'JDisc Server ID: ' . str_replace('deviceid-', '', isys_cmdb_dao_category_g_identifier::get_identifier_key()),
                            'type'         => defined_or_default('C__CATG__IDENTIFIER_TYPE__JDISC'),
                            'key'          => isys_cmdb_dao_category_g_identifier::get_identifier_key(),
                            'last_edited'  => date('Y-m-d H:i:s')
                        ];
                        isys_cmdb_dao_category_g_identifier::instance($this->m_db)
                            ->create_data($l_arr);
                        isys_cmdb_dao_category_g_identifier::remove_missing_identifier($l_object_id);
                    }
                } else {
                    $this->m_log->warning('Virtual host with device id "' . $p_id . '" not found.');
                }
            }
        } else {
            $this->m_log->warning('Virtual host with device id "' . $p_id . '" not found.');
        }
    }

    /**
     * Prepares the data from JDisc to a "i-doit-understandable" format.
     *
     * @param array $p_data
     * @param array $p_vm_con_arr     List of VM connections with guests as keys and
     *                                hosts as values.
     * @param array $p_jdisc_to_idoit Matching from JDisc device id to i-doit object id
     *
     * @return array Associative array
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_virtual_machine($p_data, $p_host_in_jdisc, $p_jdisc_to_idoit, $p_cluster_data = [])
    {
        $this->m_log->debug('> Preparing virtual machine data');

        if ($p_host_in_jdisc !== null) {
            $l_result = [
                'title'             => 'Virtuelle Maschine',
                'const'             => 'C__CATG__VIRTUAL_MACHINE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => [
                    [
                        'data_id'    => null,
                        'properties' => [
                            'virtual_machine' => [
                                'tag'        => 'virtual_machine',
                                'value'      => $this->language->get('LC__CMDB__CATG__VIRTUAL_MACHINE'),
                                'id'         => 2,
                                'title_lang' => $this->language->get('LC__CMDB__CATG__VIRTUAL_MACHINE'),
                                'title'      => 'LC__CMDB__CATG__VIRTUAL_MACHINE'
                            ]
                        ]
                    ]
                ]
            ];
            // Fill out property 'hosts' and 'primary' if vm is assigned to a cluster:
            if (isset($p_jdisc_to_idoit[$p_host_in_jdisc])) {
                $l_object_id = $p_jdisc_to_idoit[$p_host_in_jdisc];
                /** $l_cmdb_dao isys_cmdb_dao_jdisc */
                $l_cmdb_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
                if (!isset($this->m_cached_virtual_hosts[$l_object_id])) {
                    $l_object = $l_cmdb_dao->get_object_by_id($l_object_id)
                        ->__to_array();
                    $this->m_cached_virtual_hosts[$l_object_id] = [
                        'id'         => $l_object_id,
                        'title'      => $l_object['isys_obj__title'],
                        'type_const' => $l_object['isys_obj_type__const'],
                        'type_title' => $l_object['isys_obj_type__title'],
                        'sysid'      => $l_object['isys_obj__sysid'],
                    ];
                }
                if (count($p_cluster_data) > 0) {
                    // Virtual machine is assigned to cluster
                    $l_cluster_data = $p_cluster_data['category_entities'][0]['properties']['connected_object'];
                    $l_result['category_entities'][0]['properties']['hosts'] = [
                        'tag'      => 'hosts',
                        'value'    => $l_cluster_data['value'],
                        'id'       => $l_cluster_data['id'],
                        'type'     => $l_cluster_data['type'],
                        'sysid'    => $l_cluster_data['sysid'],
                        'lc_title' => $this->language->get($l_cluster_data['title']),
                        'title'    => $l_cluster_data['title']
                    ];
                    $l_result['category_entities'][0]['properties']['primary'] = [
                        'tag'      => 'primary',
                        'value'    => $this->m_cached_virtual_hosts[$l_object_id]['title'],
                        'id'       => $this->m_cached_virtual_hosts[$l_object_id]['id'],
                        'type'     => $this->m_cached_virtual_hosts[$l_object_id]['type_const'],
                        'sysid'    => $this->m_cached_virtual_hosts[$l_object_id]['sysid'],
                        'lc_title' => $this->language->get($this->m_cached_virtual_hosts[$l_object_id]['type_title']),
                        'title'    => 'LC__CMDB__HOST_IN_CLUSTER'
                    ];
                    $this->m_cached_virtual_hosts[$l_cluster_data['id']] = [
                        'id'         => $l_cluster_data['id'],
                        'title'      => $l_cluster_data['value'],
                        'type_const' => $l_cluster_data['type'],
                        'type_title' => $l_cluster_data['title'],
                        'sysid'      => $l_cluster_data['sysid'],
                    ];
                    $this->set_object_id($l_cluster_data['id']);
                } else {
                    $l_result['category_entities'][0]['properties']['hosts'] = [
                        // Taken from some imports. Probably too much information:
                        'tag'      => 'hosts',
                        'value'    => $this->m_cached_virtual_hosts[$l_object_id]['title'],
                        'id'       => $this->m_cached_virtual_hosts[$l_object_id]['id'],
                        'type'     => $this->m_cached_virtual_hosts[$l_object_id]['type_const'],
                        'sysid'    => $this->m_cached_virtual_hosts[$l_object_id]['sysid'],
                        'lc_title' => $this->language->get($this->m_cached_virtual_hosts[$l_object_id]['type_title']),
                        'title'    => 'LC__CMDB__CATG__VM__RUNNING_ON_HOST'
                    ];
                }
                // Fill out property 'hosts':
                $l_result['category_entities'][0]['properties']['system'] = [
                    'tag'        => 'system',
                    'value'      => $p_data['type_name'],
                    // This information isn't available. Workaround:
                    'id'         => $p_data['type'],
                    'title_lang' => $p_data['type_name'],
                    'title'      => $p_data['type_name']
                ];
            } else {
                // Do not update this category
                return false;
            }
        } else {
            $l_result = [
                'title'             => 'Virtuelle Maschine',
                'const'             => 'C__CATG__VIRTUAL_MACHINE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => [
                    [
                        'data_id'    => null,
                        'properties' => [
                            'virtual_machine' => [
                                'tag'        => 'virtual_machine',
                                'value'      => $this->language->get('LC__CMDB__CATG__VIRTUAL_NO'),
                                'id'         => C__VM__NO,
                                'title_lang' => $this->language->get('LC__CMDB__CATG__VIRTUAL_NO'),
                                'title'      => 'LC__CMDB__CATG__VIRTUAL_MACHINE'
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $l_result;
    }

    /**
     * Fetches all devices from database. Devices are ordered by their type
     * because JDisc already handles dependencies between each other.
     *
     * @param int       $p_group       (optional) Device group identifier
     * @param int|array $p_id          (optional) May be integer or array of integers.
     * @param array     $p_assingments (optional) Object type assignments
     * @param boolean   $p_force       (optional)
     *
     * @return resource Result set
     *
     * @author Van Quyen Hoang <qhoang@synetics.de>
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_devices_by_profile($p_group = null, $p_id = null, $p_assingments = null, $p_force = false)
    {
        $l_sql = 'SELECT d.*, STRING_AGG(dgroup.name, \',\') AS group_name, b.fwversion AS firmware, 
            d.id AS deviceid, os.osfamily, os.osversion, os.patchlevel, os.id AS osid, type.singular AS type_name%s 
          FROM device AS d 
            LEFT JOIN bios AS b ON b.id = d.biosid 
            LEFT JOIN devicegroupdevicerelation AS dg ON dg.deviceid = d.id 
            LEFT JOIN devicegroup AS dgroup ON dgroup.id = dg.devicegroupid 
            LEFT JOIN operatingsystem AS os ON d.operatingsystemid = os.id
            LEFT JOIN devicetypelookup AS type ON d.type = type.id ' . $this->m_device_filter_join . ' ' . '%s';

        $l_select = '';
        $l_condition = null;

        $l_additonal_order = ' ORDER BY type.id ';
        $l_group_by = ' GROUP BY d.id, b.id, os.id, type.id ';
        $l_casts = $l_conditions = $l_order = [];
        $l_order_counter = 1;

        if (isset($p_group)) {
            if ((int)$p_group === 1) {
                $l_conditions[] = 'dg.devicegroupid = ' . $this->convert_sql_id($p_group) . ' OR dg.devicegroupid IS NULL';
            } else {
                $l_conditions[] = 'dg.devicegroupid = ' . $this->convert_sql_id($p_group);
            }
        }

        if (isset($p_id)) {
            if (is_array($p_id) && count($p_id) > 0) {
                $l_id = array_map([
                    $this,
                    'convert_sql_id'
                ], $p_id);
                $l_conditions[] = 'd.id IN (' . implode(', ', $l_id) . ')';
            } elseif (is_numeric($p_id) && $p_id > 0) {
                $l_conditions[] = 'd.id = ' . $this->convert_sql_id($p_id);
            }
        }

        if (count($this->m_assignments) == 0 && count($p_assingments) > 0) {
            $this->m_assignments = $p_assingments;
        }

        if (isset($p_assingments)) {
            assert(is_array($p_assingments));

            $l_assignments_conditions = $l_location_assignment = [];

            foreach ($p_assingments as $l_assignment) {
                $l_assignment_conditions = [];
                $l_object_type = null;

                // i-doit object type:
                if (!isset($l_assignment['object_type'])) {
                    continue;
                } else {
                    $l_object_type = $this->convert_sql_id($l_assignment['object_type']);
                }

                // Device types:
                if (isset($l_assignment['jdisc_type_customized']) && !empty($l_assignment['jdisc_type_customized'])) {
                    $l_singular = $this->convert_sql_text(str_replace('*', '%', $l_assignment['jdisc_type_customized']));
                    $l_assignment_conditions[] = 'type.singular LIKE ' . $l_singular;

                    if (isset($l_assignment['location']) && $l_assignment['location'] > 0) {
                        $l_location_assignment[] = 'WHEN type.singular LIKE ' . $l_singular . ' THEN ' . $this->convert_sql_id($l_assignment['location']);
                    }
                } elseif (isset($l_assignment['jdisc_type']) && !empty($l_assignment['jdisc_type'])) {
                    $l_assignment_conditions[] = 'd.type = ' . $this->convert_sql_int($l_assignment['jdisc_type']);

                    if (isset($l_assignment['location']) && $l_assignment['location'] > 0) {
                        $l_location_assignment[] = 'WHEN d.type = ' . $l_assignment['jdisc_type'] . ' THEN ' . $this->convert_sql_id($l_assignment['location']);
                    }
                }

                // Operating systems:
                if (isset($l_assignment['jdisc_os_customized']) && !empty($l_assignment['jdisc_os_customized'])) {
                    $l_osversion = $this->convert_sql_text(str_replace('*', '%', $l_assignment['jdisc_os_customized']));
                    $l_assignment_conditions[] = 'os.osversion LIKE ' . $l_osversion;
                } elseif (isset($l_assignment['jdisc_os']) && !empty($l_assignment['jdisc_os'])) {
                    $l_assignment_conditions[] = 'os.osversion = ' . '(SELECT osversion FROM operatingsystem WHERE id = ' . $this->convert_sql_int($l_assignment['jdisc_os']) .
                        ')';
                }

                if (count($l_assignment_conditions) > 0) {
                    $l_assignments_conditions[] = '(' . implode(' AND ', $l_assignment_conditions) . ')';

                    $l_casts[] = 'WHEN ' . implode(' AND ', $l_assignment_conditions) . ' THEN ' . $l_object_type;

                    $l_order[] = 'WHEN ' . implode(' AND ', $l_assignment_conditions) . ' THEN ' . $l_order_counter;
                    $l_order_counter++;
                }
            } //foreach assignment

            if (count($l_assignments_conditions) > 0) {
                $l_conditions[] = '(' . implode(' OR ', $l_assignments_conditions) . ')';
            }

            if (count($l_location_assignment) > 0) {
                $l_select .= ', CASE ' . implode(' ', $l_location_assignment) . ' END AS location';
            }
        }

        if (count($l_conditions) > 0) {
            $l_condition = 'WHERE ' . implode(' AND ', $l_conditions) . ' ';

            //$l_condition .= 'AND (d.id = 62)';
            if (count($l_casts) > 0) {
                $l_select .= ', CASE ' . implode(' ', $l_casts) . ' END AS idoit_obj_type';
            }

            if (count($l_order) > 0) {
                $l_select .= ', CASE ' . implode(' ', $l_order) . ' END AS type_order';
                $l_additonal_order = 'ORDER BY type_order ASC';
            }
        }

        if (strpos($l_select, 'idoit_obj_type') === false && !$p_force) {
            return false;
        }

        $l_sql = sprintf($l_sql . $l_group_by . $l_additonal_order, $l_select, $l_condition . $this->m_device_filter_condition);

        return $this->fetch($l_sql);
    }

    /**
     * Counts how many blade chassis connections exists.
     * 15000 is the identificator that the connection is a blade chassis connection
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function count_chassis_connections()
    {
        $l_sql = 'SELECT ddc.* FROM devicedeviceconnection AS ddc
		 LEFT JOIN device AS d ON d.id = ddc.fromdeviceid
		WHERE ddc.connectortype = 15000';
        $l_sql .= ' UNION ' . 'SELECT ddc.* FROM devicedeviceconnection AS ddc
		 LEFT JOIN device AS d ON d.id = ddc.todeviceid
		WHERE ddc.connectortype = 20000 AND d.type = ' . $this->convert_sql_id($this->get_jdisc_type_id(defined_or_default('C__OBJTYPE__BLADE_CHASSIS'), 'BladeEnclosure'));

        return $this->m_pdo->num_rows($this->fetch($l_sql));
    }

    /**
     * Gets all types which are connected to a blade chassis
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_chassis_connections_types()
    {
        $l_sql = 'SELECT DISTINCT(dt.singular) FROM devicedeviceconnection
				LEFT JOIN device AS d ON todeviceid = d.id
				LEFT JOIN devicetypelookup AS dt ON dt.id = d.type
				WHERE connectortype = 15000
			UNION
			SELECT DISTINCT(dtf.singular) FROM devicedeviceconnection
				LEFT JOIN device AS d ON todeviceid = d.id
				LEFT JOIN devicetypelookup AS dt ON dt.id = d.type
				LEFT JOIN device AS df ON fromdeviceid = df.id
				LEFT JOIN devicetypelookup AS dtf ON dtf.id = df.type
			WHERE (connectortype = 20000 AND d.type = ' . $this->convert_sql_id($this->get_jdisc_type_id(defined_or_default('C__OBJTYPE__BLADE_CHASSIS'), 'BladeEnclosure')) . ')';

        $l_res = $this->fetch($l_sql);
        $l_types = '';
        while ($l_row = $this->m_pdo->fetch_row($l_res)) {
            $l_types .= current($l_row) . ',';
        }

        return rtrim($l_types, ',');
    }

    /**
     * Checks if device has any connections to a chassis type which are defined in the profile
     *
     * @param    $p_id    int
     *
     * @return    bool
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_blade_connection($p_id, $p_chassis_types)
    {
        $l_sql = 'SELECT ddc.fromdeviceid, ddc.displayname FROM devicedeviceconnection AS ddc
			LEFT JOIN device AS d ON ddc.fromdeviceid = d.id
			WHERE (ddc.connectortype = 15000) AND ddc.todeviceid = ' . $this->convert_sql_id($p_id);

        $l_sql .= ' UNION ' . 'SELECT ddc.todeviceid AS fromdeviceid, ddc.displayname FROM devicedeviceconnection AS ddc
			LEFT JOIN device AS d ON ddc.todeviceid = d.id
			WHERE (ddc.connectortype = 20000 AND d.type IN (' . implode(',', $p_chassis_types) . '))
			AND ddc.fromdeviceid = ' . $this->convert_sql_id($p_id);

        $l_res = $this->fetch($l_sql);
        if ($l_res) {
            return $this->m_pdo->fetch_row($l_res);
        }

        return false;
    }

    /**
     * Sets blade connection into cache array
     *
     * $p_blade_info[0] = blade id
     * $p_blade_info[1]    = Slot display name
     *
     * @param $p_blade_id
     * @param $p_device_id
     */
    public function set_blade_connection($p_blade_info, $p_device_id)
    {
        $this->m_cached_blade_connections[$p_blade_info[0]][$p_device_id] = $p_blade_info[1];
    }

    /**
     * Gets cached blade connections
     *
     * @return array
     */
    public function get_cached_blade_connection()
    {
        return $this->m_cached_blade_connections;
    }

    /**
     * Create blade connections between blade and devices
     *
     * @param $p_jdisc_to_idoit_objects
     */
    public function create_blade_connections($p_jdisc_to_idoit_objects)
    {
        if (count($this->m_cached_blade_connections) > 0) {
            $l_dao_chassis = isys_cmdb_dao_category_s_chassis::instance($this->m_db);
            $l_dao_chassis_view = isys_cmdb_dao_category_s_chassis_view::instance($this->m_db);
            $l_dao_chassis_slot = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db);

            $l_dao_objecttype = isys_cmdb_dao_object_type::factory($this->get_database_component());

            $this->m_log->info('Chassis connections found. Creating or updating connections.');
            $this->m_log->debug('Creating chassis connections');

            foreach ($this->m_cached_blade_connections as $l_blade_id => $l_slot_data) {
                if (!isset($p_jdisc_to_idoit_objects[$l_blade_id])) {
                    if (($l_id = isys_jdisc_dao_matching::instance()
                        ->get_object_id_by_device_id($l_blade_id))) {
                        $this->m_log->debug('Device id ' . $l_blade_id . ' found via matching. Using ' . $l_id . ' as chassis/blade object id.');
                    } else {
                        $l_blade_res = $this->get_devices(null, $l_blade_id);
                        $l_blade_info = $this->m_pdo->fetch_row_assoc($l_blade_res);
                        $l_id = $l_dao_chassis->get_obj_id_by_title($l_blade_info['name']);
                        if (!$l_dao_objecttype->has_cat($l_dao_chassis->get_objTypeID($l_id), ['C__CATS__CHASSIS'])) {
                            $l_id = false;
                        } else {
                            // add id to the cache
                            isys_cmdb_dao_category_g_identifier::set_object_id_by_identifier($l_id, $l_blade_id);
                        }
                    }
                    $p_jdisc_to_idoit_objects[$l_blade_id] = $l_id;
                } else {
                    $l_id = $p_jdisc_to_idoit_objects[$l_blade_id];
                }

                if ($l_id !== false) {
                    $l_blade_object_id = $l_id;

                    $l_slots_amount = count($l_slot_data);

                    $l_chassis_data_res = $l_dao_chassis_view->get_data(null, $l_blade_object_id);
                    if ($l_chassis_data_res->num_rows() == 0) {
                        $l_data = [
                            'isys_obj__id' => $l_blade_object_id,
                            'status'       => C__RECORD_STATUS__NORMAL,
                            'front_x'      => 1,
                            'front_y'      => $l_slots_amount,
                        ];
                        $l_dao_chassis_view->create_data($l_data);
                    } else {
                        $l_catdata = $l_chassis_data_res->get_row();
                        $l_data = [
                            'status'       => C__RECORD_STATUS__NORMAL,
                            'id'           => $l_catdata['isys_cats_chassis_view_list__id'],
                            'isys_obj__id' => $l_blade_object_id,
                            'front_x'      => ($l_catdata['isys_cats_chassis_view_list__front_width'] > 1 ? $l_catdata['isys_cats_chassis_view_list__front_width'] : 1),
                            'front_y'      => ($l_catdata['isys_cats_chassis_view_list__front_height'] > 0 ? $l_catdata['isys_cats_chassis_view_list__front_height'] : $l_slots_amount),
                            'front_size'   => $l_catdata['isys_cats_chassis_view_list__front_size'],
                            'rear_x'       => $l_catdata['isys_cats_chassis_view_list__rear_width'],
                            'rear_y'       => $l_catdata['isys_cats_chassis_view_list__rear_height'],
                            'rear_size'    => $l_catdata['isys_cats_chassis_view_list__rear_size'],
                            'description'  => $l_catdata['isys_cats_chassis_view_list__description']
                        ];
                        $l_dao_chassis_view->save_data($l_catdata['isys_cats_chassis_view_list__id'], $l_data);
                    }

                    $l_assigned_devices = $l_dao_chassis->get_assigned_objects($l_blade_object_id, C__RECORD_STATUS__NORMAL, true);

                    $this->m_log->info($l_slots_amount . ' chassis connections found.');
                    $this->m_log->debug('Chassis device with ID ' . $l_blade_id . ' has been found.');
                    foreach ($l_slot_data as $l_device_id => $l_slot_title) {
                        $l_device_object_id = $p_jdisc_to_idoit_objects[$l_device_id] ?: (isys_jdisc_dao_matching::instance()
                            ->get_object_id_by_device_id($l_device_id) ?: false);

                        if ($l_device_object_id) {
                            $l_add_to_slot = false;

                            // Check if object is already assigned to chassis
                            if (!in_array($l_device_object_id, $l_assigned_devices)) {
                                $this->m_log->info('Device with ID ' . $l_device_id . ' has been found. Creating connection between blade device (' . $l_blade_id .
                                    ') and device (' . $l_device_id . ').');

                                // @see  ID-5528  Add a logbook entry.
                                isys_component_dao_logbook::instance($this->m_db)
                                    ->set_entry(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        '',
                                        date('Y-m-d H:i:s'),
                                        defined_or_default(C__LOGBOOK__ALERT_LEVEL__0, 1),
                                        $l_blade_object_id,
                                        $l_dao_chassis->get_obj_name_by_id_as_string($l_blade_object_id),
                                        $l_dao_chassis->get_obj_type_name_by_obj_id($l_blade_object_id),
                                        'LC__CMDB__CATS__CHASSIS_DEVICES',
                                        defined_or_default('C__LOGBOOK_SOURCE__JDISC'),
                                        serialize([
                                            'isys_cmdb_dao_category_s_chassis::assigned_device' => [
                                                'from' => null,
                                                'to'   => $l_dao_chassis->get_obj_name_by_id_as_string($l_device_object_id)
                                            ]
                                        ])
                                    );

                                // add device to chassis
                                $l_cats_chassis_id = $l_dao_chassis->create($l_blade_object_id, C__RECORD_STATUS__NORMAL, null, null, $l_device_object_id);
                                $l_add_to_slot = true;
                            } else {
                                $l_cats_chassis_id = $l_dao_chassis->get_data(
                                    null,
                                    $l_blade_object_id,
                                    'AND isys_connection__isys_obj__id = ' . $l_dao_chassis->convert_sql_id($l_device_object_id)
                                )
                                    ->get_row_value('isys_cats_chassis_list__id');
                            }

                            // Check if object has the slot
                            $l_res_slot = $l_dao_chassis_slot->get_data(
                                null,
                                $l_blade_object_id,
                                'AND isys_cats_chassis_slot_list__title = ' . $l_dao_chassis_slot->convert_sql_text($l_slot_title)
                            );
                            if ($l_res_slot->num_rows() == 0) {
                                $l_slot_create_data = [
                                    'isys_obj__id'   => $l_blade_object_id,
                                    'title'          => $l_slot_title,
                                    'description'    => '',
                                    'insertion'      => C__INSERTION__FRONT,
                                    'connector_type' => 1,
                                ];

                                $connectorTypeName = $l_dao_chassis
                                    ->retrieve('SELECT isys_chassis_connector_type__title AS title FROM isys_chassis_connector_type WHERE isys_chassis_connector_type__id = 1;')
                                    ->get_row_value('title');

                                // @see  ID-5528  Add a logbook entry.
                                isys_component_dao_logbook::instance($this->m_db)
                                    ->set_entry(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        '',
                                        date('Y-m-d H:i:s'),
                                        defined_or_default(C__LOGBOOK__ALERT_LEVEL__0, 1),
                                        $l_blade_object_id,
                                        $l_dao_chassis->get_obj_name_by_id_as_string($l_blade_object_id),
                                        $l_dao_chassis->get_obj_type_name_by_obj_id($l_blade_object_id),
                                        'LC__CMDB__CATS__CHASSIS_SLOTS',
                                        defined_or_default('C__LOGBOOK_SOURCE__JDISC'),
                                        serialize([
                                            'isys_cmdb_dao_category_s_chassis_slot::title' => [
                                                'from' => null,
                                                'to'   => $l_slot_title
                                            ],
                                            'isys_cmdb_dao_category_s_chassis_slot::insertion' => [
                                                'from' => null,
                                                'to'   => $this->language->get('LC__UNIVERSAL__FRONT')
                                            ],
                                            'isys_cmdb_dao_category_s_chassis_slot::connector_type' => [
                                                'from' => null,
                                                'to'   => $this->language->get($connectorTypeName)
                                            ]
                                        ])
                                    );

                                $l_slot_id = $l_dao_chassis_slot->create_data($l_slot_create_data);
                                $l_add_to_slot = true;
                            } else {
                                $l_slot_local_data = $l_res_slot->get_row();
                                $l_slot_id = $l_slot_local_data['isys_cats_chassis_slot_list__id'];
                            }

                            $l_assigned_slot = $l_dao_chassis->get_assigned_slots_by_cat_id($l_cats_chassis_id);
                            if ($l_add_to_slot || (count($l_assigned_slot) == 0 && !$l_add_to_slot)) {
                                // assign slot to assigned device
                                $l_dao_chassis->assign_slot_to_chassis_item($l_cats_chassis_id, $l_slot_id);
                            }
                        } else {
                        }
                    }
                } else {
                    $this->m_log->debug('Chassis device with ID ' . $l_blade_id . ' was not found. And needs to be imported to create the connection.');
                }
            }
        }
    }

    /**
     * Collect all devices which has modules
     *
     * @param $p_id
     */
    public function prepare_modules($p_id)
    {
        $l_sql = 'SELECT DISTINCT(mo.serialnumber) AS mod_serial FROM module AS mo
			INNER JOIN mac AS m ON mo.id = m.moduleid
			WHERE mo.model != \'\' AND mo.serialnumber != \'\' AND m.deviceid = ' . $this->convert_sql_id($p_id) . ' LIMIT 1;';
        $l_res = $this->fetch($l_sql);
        if ($this->m_pdo->num_rows($l_res) > 0) {
            if (!is_object($this->m_module_slots)) {
                $this->m_module_slots = new isys_array([]);
            }
            $this->m_module_slots->append($p_id);
        }
    }

    /**
     * Creates the connection between jdisc module and chassis device
     * (Special case for Cisco Switches for example)
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_database
     * @todo statt $p_obj_ids in isys_cache_runtime. Alle Stellen identifizieren und isys_cache_runtime verwenden
     */
    public function create_module_connections($p_obj_ids, $p_interface_import_type = 0)
    {
        if ($this->m_module_slots instanceof isys_array && $this->m_module_slots->count() > 0) {
            $this->m_log->debug('Starting Chassis connections.');
            $this->set_additional_info();
            $l_current = $this->m_module_slots->current();

            $l_dao = isys_cmdb_dao_category_g_model::instance($this->m_db);
            $l_dao_dialog_admin = isys_cmdb_dao_dialog_admin::instance($this->m_db);
            $l_dao_slot = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db);
            $l_dao_chassis = isys_cmdb_dao_category_s_chassis::instance($this->m_db);
            $l_dao_obj = isys_cmdb_dao_object_type::instance($this->m_db);
            $l_dao_loc = isys_cmdb_dao_category_g_location::instance($this->m_db);
            $l_archive_objects = [];

            do {
                $l_device_id = $l_current;

                if (isset($p_obj_ids[$l_device_id])) {
                    $l_chassis_object_id = $p_obj_ids[$l_device_id];
                } else {
                    $l_chassis_object_id = isys_jdisc_dao_matching::instance()
                        ->get_object_id_by_device_id($l_device_id);
                }

                if ($l_chassis_object_id > 0 && $l_chassis_object_id !== false) {
                    // Check if Object has the category chassis
                    $l_obj_type_id = $l_dao->get_objTypeID($l_chassis_object_id);

                    if (!isset($this->m_object_type_categories[$l_obj_type_id])) {
                        $this->m_object_type_categories[$l_obj_type_id] = $l_dao_obj->has_cat($l_obj_type_id, ['C__CATS__CHASSIS']);
                    }

                    if (!$this->m_object_type_categories[$l_obj_type_id]) {
                        $l_current = $this->m_module_slots->next();
                        if ($l_current) {
                            continue;
                        } else {
                            break;
                        }
                    }

                    $l_slots = new isys_array([]);
                    // retrieve slots from chassis
                    $l_sql = 'SELECT isys_cats_chassis_slot_list__id AS id, isys_cats_chassis_slot_list__title AS title FROM isys_cats_chassis_slot_list
						WHERE isys_cats_chassis_slot_list__isys_obj__id = ' . $this->convert_sql_id($l_chassis_object_id);
                    $l_res = $this->retrieve($l_sql);
                    if ($l_res->num_rows() > 0) {
                        while ($l_row = $l_res->get_row()) {
                            $l_slots->offsetSet($l_row['title'], $l_row['id']);
                        }
                    }

                    $l_modules = $this->get_modules_info($l_device_id);

                    $l_sql = 'SELECT isys_connection__isys_obj__id, isys_catg_location_list__id, c.isys_cats_chassis_list__id FROM isys_cats_chassis_list AS c
									INNER JOIN isys_connection ON isys_connection__id = c.isys_cats_chassis_list__isys_connection__id
									INNER JOIN isys_catg_location_list ON isys_connection__isys_obj__id = isys_catg_location_list__isys_obj__id
									LEFT JOIN isys_cats_chassis_list_2_isys_cats_chassis_slot_list AS co
									ON co.isys_cats_chassis_list__id = c.isys_cats_chassis_list__id
									WHERE c.isys_cats_chassis_list__isys_obj__id = ' . $this->convert_sql_id($l_chassis_object_id);
                    $l_res = $this->retrieve($l_sql);
                    $l_assigned_objects = [];
                    if ($l_res->num_rows() > 0) {
                        while ($l_row = $l_res->get_row()) {
                            $l_assigned_objects[$l_row['isys_connection__isys_obj__id']] = [
                                $l_row['isys_catg_location_list__id'],
                                $l_row['isys_cats_chassis_list__id']
                            ];
                        }
                    }

                    // Overwrite
                    if (isys_jdisc_dao_data::clear_data() === true) {
                        $this->m_log->debug('Chassis connections cleared');
                        $l_dao->clear_data($l_chassis_object_id, 'isys_cats_chassis_list', true);
                        // remove from location
                        if (count($l_assigned_objects) > 0) {
                            foreach ($l_assigned_objects as $l_module_object_id => $l_module_data) {
                                if ($l_module_data[0] > 0) {
                                    $l_dao_loc->save($l_module_data[0], $l_module_object_id, null, $l_chassis_object_id);
                                }
                            }
                        }
                    }

                    if ($p_interface_import_type === 0) {
                        $this->m_log->debug('Chassis connections skipped');
                        continue;
                    }

                    if ($l_modules && is_array($l_modules)) {
                        foreach ($l_modules as $l_module) {
                            $l_new_object = false;
                            $l_assigned = false;
                            $l_chassis_id = false;
                            $l_assigned_slot_id = false;
                            $l_serial = trim($l_module['serial']);
                            $l_raw_title = trim($l_module['title']);
                            $l_title = $l_raw_title . (($l_serial != '') ? ' - ' . $l_serial : '');
                            $l_manufacturer = trim($l_module['manufacturer']);
                            $l_description = trim($l_module['description']);
                            $l_slot = trim($l_module['slot']);
                            $l_os = trim($l_module['os']);
                            $l_firmware = trim($l_module['firmware']);
                            // First Check if device module exists
                            // We cannot retrieve objects by device id because the modules don´t have a device id
                            $l_object_id = isys_jdisc_dao_matching::instance()
                                ->get_object_id_by_module_id($l_module['moduleid']);

                            // $l_object_id = $l_dao->get_object_by_hostname_serial_mac(null, $l_serial, null, $l_title);
                            if ($l_object_id === false) {
                                // Create Object as switch blade because we know this object has ports
                                $l_object_id = $l_dao->insert_new_obj($this->m_module_objecttype, false, $l_title, null, C__RECORD_STATUS__NORMAL);

                                isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                                    ->set_identifier(
                                        $l_object_id,
                                        defined_or_default('C__CATG__IDENTIFIER_TYPE__JDISC'),
                                        isys_cmdb_dao_category_g_identifier::get_identifier_key(),
                                        'module-' . $l_module['moduleid']
                                    );

                                if ($this->m_model_dialog_cache->has($l_manufacturer)) {
                                    $l_parent_id = $this->m_model_dialog_cache->get($l_manufacturer)
                                        ->get('id');
                                    if ($this->m_model_dialog_cache->get($l_manufacturer)
                                        ->get('childs')
                                        ->has($l_title)) {
                                        $l_child_id = $this->m_model_dialog_cache->get($l_manufacturer)
                                            ->get('childs')
                                            ->get($l_title);
                                    } else {
                                        $l_child_id = $l_dao_dialog_admin->create('isys_model_title', $l_title, 5, null, C__RECORD_STATUS__NORMAL, $l_parent_id);
                                        $this->m_model_dialog_cache->get($l_manufacturer)
                                            ->get('childs')
                                            ->offsetSet($l_title, $l_child_id);
                                    }
                                } else {
                                    // Create new entry
                                    $l_parent_id = $l_dao_dialog_admin->create('isys_model_manufacturer', $l_manufacturer, 5, null, C__RECORD_STATUS__NORMAL);
                                    $l_child_id = $l_dao_dialog_admin->create('isys_model_title', $l_title, 5, null, C__RECORD_STATUS__NORMAL, $l_parent_id);
                                    $this->m_model_dialog_cache->offsetSet($l_manufacturer, new isys_array([
                                        'id'     => $l_parent_id,
                                        'childs' => new isys_array([$l_title => $l_child_id])
                                    ]));
                                }
                                $l_dao->create($l_object_id, $l_parent_id, $l_child_id, null, $l_serial, $l_firmware, $l_description);
                                $l_new_object = true;
                            }
                            // Second Check if device is already assigned
                            if (!$l_new_object) {
                                // See ID-4458
                                if ($this->moduleUpdateObjtype === true) {
                                    $l_dao->update_object($l_object_id, $this->m_module_objecttype);
                                    $this->m_log->debug('>> Updating object type of Module ' . $l_title);
                                }

                                // Check if object is already assigned
                                $l_sql = 'SELECT c.isys_cats_chassis_list__id, co.isys_cats_chassis_slot_list__id FROM isys_cats_chassis_list AS c
									INNER JOIN isys_connection ON isys_connection__id = c.isys_cats_chassis_list__isys_connection__id
									LEFT JOIN isys_cats_chassis_list_2_isys_cats_chassis_slot_list AS co
									ON co.isys_cats_chassis_list__id = c.isys_cats_chassis_list__id
									WHERE c.isys_cats_chassis_list__isys_obj__id = ' . $this->convert_sql_id($l_chassis_object_id) . '
									AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($l_object_id);
                                $l_res = $this->retrieve($l_sql);
                                if ($l_res->num_rows() > 0) {
                                    $l_entry_data = $l_res->get_row();
                                    $l_chassis_id = $l_entry_data['isys_cats_chassis_list__id'];
                                    $l_assigned_slot_id = $l_entry_data['isys_cats_chassis_slot_list__id'];
                                    $l_assigned = true;
                                }
                            }
                            // Check if slot exists
                            if (!$l_slots->has($l_slot)) {
                                // Add slot
                                $l_data = [
                                    'isys_obj__id'   => $l_chassis_object_id,
                                    'title'          => $l_slot,
                                    'description'    => '',
                                    'insertion'      => null,
                                    'connector_type' => null
                                ];

                                // @see  ID-5528  Add a logbook entry.
                                isys_component_dao_logbook::instance($this->m_db)
                                    ->set_entry(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        '',
                                        date('Y-m-d H:i:s'),
                                        defined_or_default(C__LOGBOOK__ALERT_LEVEL__0, 1),
                                        $l_chassis_object_id,
                                        $l_dao_chassis->get_obj_name_by_id_as_string($l_chassis_object_id),
                                        $l_dao_chassis->get_obj_type_name_by_obj_id($l_chassis_object_id),
                                        'LC__CMDB__CATS__CHASSIS_SLOTS',
                                        defined_or_default('C__LOGBOOK_SOURCE__JDISC'),
                                        serialize([
                                            'isys_cmdb_dao_category_s_chassis_slot::title' => [
                                                'from' => null,
                                                'to'   => $l_slot
                                            ]
                                        ])
                                    );

                                $l_slot_id = $l_dao_slot->create_data($l_data);
                            } else {
                                $l_slot_id = $l_slots->get($l_slot);
                            }
                            // Third assign object to chassis object if not assigned
                            if (!$l_assigned) {
                                // @see  ID-5528  Add a logbook entry.
                                isys_component_dao_logbook::instance($this->m_db)
                                    ->set_entry(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        '',
                                        date('Y-m-d H:i:s'),
                                        defined_or_default(C__LOGBOOK__ALERT_LEVEL__0, 1),
                                        $l_chassis_object_id,
                                        $l_dao_chassis->get_obj_name_by_id_as_string($l_chassis_object_id),
                                        $l_dao_chassis->get_obj_type_name_by_obj_id($l_chassis_object_id),
                                        'LC__CMDB__CATS__CHASSIS_DEVICES',
                                        defined_or_default('C__LOGBOOK_SOURCE__JDISC'),
                                        serialize([
                                            'isys_cmdb_dao_category_s_chassis::assigned_device' => [
                                                'from' => null,
                                                'to'   => $l_dao_chassis->get_obj_name_by_id_as_string($l_object_id)
                                            ],
                                            'C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT' => [
                                                'from' => null,
                                                'to' => $l_slot
                                            ]
                                        ])
                                    );

                                $l_dao_slot->assign_chassis_item_to_slot(
                                    $l_dao_chassis->create($l_chassis_object_id, C__RECORD_STATUS__NORMAL, null, null, $l_object_id),
                                    $l_slot_id
                                );
                                $this->m_log->debug('>> Device "' . $l_title . ' (' . $l_object_id . ') ' . '" has been assigned to object "' . $l_chassis_object_id .
                                    '" in slot "' . $l_slot . '" !');
                            } elseif ($l_chassis_id !== false && $l_assigned_slot_id !== false) {
                                if ($l_assigned_slot_id != $l_slot_id) {
                                    // remove old slot connection
                                    $l_dao_chassis->remove_slot_assignments($l_chassis_id);

                                    // @see  ID-5528  Add a logbook entry.
                                    isys_component_dao_logbook::instance($this->m_db)
                                        ->set_entry(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            '',
                                            date('Y-m-d H:i:s'),
                                            defined_or_default(C__LOGBOOK__ALERT_LEVEL__0, 1),
                                            $l_chassis_object_id,
                                            $l_dao_chassis->get_obj_name_by_id_as_string($l_chassis_object_id),
                                            $l_dao_chassis->get_obj_type_name_by_obj_id($l_chassis_object_id),
                                            'LC__CMDB__CATS__CHASSIS_DEVICES',
                                            defined_or_default('C__LOGBOOK_SOURCE__JDISC'),
                                            serialize([
                                                'isys_cmdb_dao_category_s_chassis::assigned_device' => [
                                                    'from' => null,
                                                    'to'   => $l_dao_chassis->get_obj_name_by_id_as_string($l_object_id)
                                                ],
                                                'C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT' => [
                                                    'from' => null,
                                                    'to' => $l_slot
                                                ]
                                            ])
                                        );

                                    // add new slot connection
                                    $l_dao_slot->assign_chassis_item_to_slot($l_chassis_id, $l_slot_id);
                                    $this->m_log->debug('>> Assigned slot for device "' . $l_title . ' (' . $l_serial . ') ' . '" changed to "' . $l_slot . '" !');
                                }
                            }
                            unset($l_assigned_objects[$l_object_id]);
                            unset($l_archive_objects[$l_object_id]);
                        }
                        $l_archive_objects += $l_assigned_objects;
                    }
                }
            } while ($l_current = $this->m_module_slots->next());

            // Archive not assigned objects
            if (count($l_archive_objects) > 0) {
                foreach ($l_archive_objects as $l_object_id => $l_module_data) {
                    if ($l_module_data[1] > 0) {
                        $l_dao->delete_entry($l_module_data[1], 'isys_cats_chassis_list');
                    }

                    if ($l_module_data[0] > 0) {
                        $l_dao_loc->save($l_module_data[0], $l_object_id, null);
                    }

                    $l_dao->set_object_status($l_object_id, C__RECORD_STATUS__ARCHIVED);
                }
            }
        }
    }

    /**
     * Method which
     *
     * @throws Exception
     * @throws isys_exception_database
     */
    public function set_additional_info()
    {
        // Set model dialog
        if (!is_object($this->m_model_dialog_cache)) {
            $this->m_model_dialog_cache = new isys_array([]);
        }
        $l_sql = 'SELECT isys_model_manufacturer__id, isys_model_manufacturer__title FROM isys_model_manufacturer
			WHERE isys_model_manufacturer__status = ' . C__RECORD_STATUS__NORMAL;
        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $this->m_model_dialog_cache->offsetSet($this->language->get($l_row['isys_model_manufacturer__title']), new isys_array([
                'id'     => $l_row['isys_model_manufacturer__id'],
                'childs' => new isys_array([])
            ]));
        }
        $l_sql = 'SELECT isys_model_title__id, isys_model_title__title, isys_model_title__isys_model_manufacturer__id, isys_model_manufacturer__title FROM isys_model_title
			INNER JOIN isys_model_manufacturer ON isys_model_manufacturer__id = isys_model_title__isys_model_manufacturer__id
			WHERE isys_model_title__status = ' . C__RECORD_STATUS__NORMAL;
        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            if ($this->m_model_dialog_cache->has($this->language->get($l_row['isys_model_manufacturer__title']))) {
                $this->m_model_dialog_cache->get($this->language->get($l_row['isys_model_manufacturer__title']))
                    ->get('childs')
                    ->offsetSet($this->language->get($l_row['isys_model_title__title']), $l_row['isys_model_title__id']);
            }
        }
    }

    /**
     * Handle guest system data
     *
     * @param      $p_device_id
     * @param      $p_jdisc_to_idoit
     * @param bool $p_raw
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_general
     */
    public function handle_guest_systems($p_device_id, $p_object_id, $p_object_type_info, &$p_jdisc_to_idoit, $p_mode, $p_raw = false)
    {
        $this->m_log->debug('> Handling guest system data');

        /**
         * @var $l_dao isys_cmdb_dao_category_g_guest_systems
         */
        $l_dao = isys_cmdb_dao_category_g_guest_systems::instance($this->m_db);

        $l_res = $this->get_virtual_machine_connections(null, $p_device_id, true);
        $l_new_selection = $l_old_selection = '';

        $l_current_objects = [];

        $l_tmpDao = $l_dao->get_data(null, $p_object_id);

        while ($l_row = $l_tmpDao->get_row()) {
            $l_current_objects[$l_row['isys_obj__id']] = $l_row;
            $l_old_selection .= $l_row['isys_obj__title'] . ', ';
        }

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_vm_device_id = $l_row['toid'];

            if (isset($p_jdisc_to_idoit[$l_vm_device_id])) {
                $l_vm_obj_id = $p_jdisc_to_idoit[$l_vm_device_id];
            } elseif ($l_vm_obj_id = isys_jdisc_dao_matching::instance()
                ->get_object_id_by_device_id($l_vm_device_id)) {
                $this->m_log->debug('Device ID ' . $l_vm_device_id . ' found as guest system. For Host ID ' . $p_device_id . '.');
            } else {
                continue;
            }

            if ($l_vm_obj_id > 0) {
                // Check if already assigned
                if (isset($l_current_objects[$l_vm_obj_id])) {
                    $l_new_selection .= $l_current_objects[$l_vm_obj_id]['isys_obj__title'] . ', ';
                    unset($l_current_objects[$l_vm_obj_id]);
                    continue;
                }

                // Create guest system entry
                $l_dao->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_vm_obj_id, '');
                $l_new_selection .= $l_dao->obj_get_title_by_id_as_string($l_vm_obj_id) . ', ';
            }
        }

        if (count($l_current_objects) > 0 && $p_mode !== isys_import_handler_cmdb::C__APPEND) {
            // remove the objects which are not assigned anymore to the host
            foreach ($l_current_objects as $l_vm_object_id => $l_vm_data) {
                // Update VM
                $l_dao->save($l_vm_data["isys_catg_virtual_machine_list__id"], C__RECORD_STATUS__NORMAL, null, null);

                // Remove relation object between vm and host
                if ($l_vm_data["isys_catg_relation_list__isys_obj__id"] > 0) {
                    $l_dao->delete_object($l_vm_data["isys_catg_relation_list__isys_obj__id"]);
                }
            }
        }

        if (($l_old_selection != '' || $l_new_selection != '') && ($l_old_selection != $l_new_selection)) {
            $l_serialized_changes = serialize($l_changes_array = [
                'isys_cmdb_dao_category_g_guest_systems::connected_object' => [
                    'from' => $l_old_selection,
                    'to'   => $l_new_selection
                ]
            ]);

            self::set_logbook_entries([
                'object_id'      => $p_object_id,
                'object_type_id' => $p_object_type_info['id'],
                'category'       => $this->language->get('LC__CMDB__CATG__GUEST_SYSTEMS'),
                'changes'        => $l_serialized_changes,
                'count_changes'  => 1
            ]);
        }

        return true;
    }

    /**
     * Get management device connections
     *
     * @return isys_array|null
     */
    public function get_management_device_connections()
    {
        if ($this->m_cached_management_device_connection === null) {
            $this->map_management_device_connection();
        }

        return $this->m_cached_management_device_connection;
    }

    /**
     * Prepare the import data for category Remote Management Controller
     *
     * @param   array $p_data
     *
     * @return  array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function prepare_rm_controller($p_id, &$p_jdisc_to_idoit, $p_raw = false)
    {
        $this->m_log->debug('> Preparing remote management controller data');
        $l_return = [];
        $l_add_it = false;

        if ($this->m_cached_management_device_connection === null) {
            $this->map_management_device_connection();
        }

        if (isset($this->m_cached_management_device_connection[$p_id])) {
            /**
             * @var $l_dao isys_cmdb_dao_category_g_rm_controller
             */
            $l_dao = isys_cmdb_dao_category_g_rm_controller::instance($this->m_db);

            $l_management_device_id = $this->m_cached_management_device_connection[$p_id];

            if (isset($p_jdisc_to_idoit[$l_management_device_id])) {
                $l_management_object_id = $p_jdisc_to_idoit[$l_management_device_id];
            } elseif ($l_management_object_id = isys_jdisc_dao_matching::instance()
                ->get_object_id_by_device_id($l_management_device_id)) {
                $this->m_log->debug('Found management device id via object matching.');
                $l_add_it = true;
            } else {
                $this->m_log->debug('Management device with id ' . $l_management_device_id . ' has not been imported.');

                return false;
            }

            if ($l_management_object_id) {
                // Add it to the Device 2 Object-ID mapping for the import
                if ($l_add_it) {
                    $p_jdisc_to_idoit[$l_management_device_id] = $l_management_object_id;
                }

                self::set_object_id($l_management_object_id);

                $l_management_object_data = $l_dao->get_object_by_id($l_management_object_id)
                    ->get_row();

                $l_return[] = [
                    'data_id'    => null,
                    'properties' => [
                        'connected_object' => [
                            'tag'        => 'connected_object',
                            'value'      => $l_management_object_data['isys_obj__title'],
                            'id'         => $l_management_object_id,
                            'type'       => $l_management_object_data['isys_obj__isys_obj_type__id'],
                            'sysid'      => $l_management_object_data['isys_obj__sysid'],
                            'title_lang' => $l_management_object_data['isys_obj_type__title'],
                            'title'      => 'LC__CMDB__CATG__RM_CONTROLLER'
                        ]
                    ]
                ];
            } else {
                // Object has not been imported at the current time
                return false;
            }
        }

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get('LC__CMDB__CATG__RM_CONTROLLER'),
                'const'             => 'C__CATG__RM_CONTROLLER',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * This method gets the last login user by device
     * The query is from the view discovereduserconfiguration
     *
     * duosr.role = 3 = History logged on User
     * duosr.role = 2 = User
     * duosr.role = 1 = Logged on User
     *
     * Windows + Unix :
     *   InteractiveLogon (1, "Interactive"),
     *
     *   Nach reihenfolge:
     *   Windows (Remote login)
     *   TerminalServices (10, InteractiveLogon, "Terminal Services"),
     *   TerminalServicesICA (11, TerminalServices, "Citrix ICA"),
     *   TerminalServicesRDP (12, TerminalServices, "Microsoft RDP"),
     *
     *   Unix (Remote logins)
     *   Ssh (30, "SSH"),
     *   Telnet (31, "Telnet");
     *
     *   Types which are ignored:
     *
     *   Unix (Services):
     *   Daemon (20, "Daemon"),
     *   Windows (Services):
     *   Service (21, Daemon, "Service"),
     *   Batch (22, Daemon, "Batch"),
     *
     * @param $p_id
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_last_login_user_by_device_id($p_id)
    {
        $l_sql = 'SELECT
    acc.account AS account,
    dtl.name AS type
   FROM discovereduser du,
    account acc,
    discovereduseroperatingsystemrelation duosr
   LEFT JOIN device d ON d.operatingsystemid = duosr.operatingsystemid
   LEFT JOIN discoveredusertypelookup AS dtl ON dtl.id = duosr.type
  WHERE du.id = duosr.discovereduserid AND du.accountid = acc.id
  AND duosr.type IS NOT NULL AND duosr.role = 3
  AND duosr.type NOT IN (20, 21, 22)
  AND d.id = ' . $this->convert_sql_id($p_id) . ' ORDER BY duosr.type ASC, duosr.lastlogin DESC LIMIT 1;';

        $l_res = $this->fetch($l_sql);
        if ($l_res) {
            return $this->m_pdo->fetch_array($l_res);
        }

        return false;
    }

    /**
     * Prepare last login user data
     *
     * @param $p_row
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function prepare_last_login_user($p_row)
    {
        $this->m_log->debug('> Preparing last login user data');

        $l_data = $this->get_last_login_user_by_device_id($p_row['id']);

        return [
            C__DATA__TITLE      => $this->language->get('LC__CATG__LAST_LOGIN_USER'),
            'const'             => 'C__CATG__LAST_LOGIN_USER',
            'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
            'category_entities' => [
                [
                    'data_id'    => null,
                    'properties' => [
                        'last_login' => [
                            'tag'        => 'last_login',
                            'value'      => $l_data['account'],
                            'title_lang' => $l_data['account'],
                            'title'      => 'LC__CATG__LAST_LOGIN_USER__LAST_LOGIN',
                        ],
                        'type'       => [
                            'tag'        => 'type',
                            'value'      => $l_data['type'],
                            'title_lang' => $l_data['type'],
                            'title'      => 'LC__CATG__LAST_LOGIN_USER__TYPE',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Map management device connections
     */
    private function map_management_device_connection()
    {
        // Connector Type 20002 = AttachedManagementDevice
        $l_sql = 'SELECT fromdeviceid, todeviceid FROM devicedeviceconnection WHERE connectortype = \'20002\';';

        $this->m_cached_management_device_connection = new isys_array();

        $l_res = $this->fetch($l_sql);
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows');

        // fromdeviceid = management device
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $this->m_cached_management_device_connection[$l_row['todeviceid']] = $l_row['fromdeviceid'];
        }
    }

    /**
     * Prepare Stack members
     *
     * @param      $p_id
     * @param      $p_jdisc_to_idoit
     * @param bool $p_raw
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function prepare_stack_member($p_id, &$p_jdisc_to_idoit, $p_raw = false)
    {
        $l_dao = isys_cmdb_dao_category_g_stack_member::instance(isys_application::instance()->database);
        $l_stack_members = $l_return = [];

        // Retrieve already assigned stack members
        $l_res = $l_dao->get_connected_objects($this->get_current_object_id());
        while ($l_row = $l_res->get_row()) {
            $l_stack_members[$l_row['isys_catg_stack_member_list__stack_member']] = $l_row['isys_catg_stack_member_list__id'];
        }

        // Connector Type 20003 = Attached stacked switches
        $l_sql = 'SELECT fromdeviceid, todeviceid FROM devicedeviceconnection WHERE connectortype = \'20003\' ' . 'AND fromdeviceid = ' . $this->convert_sql_id($p_id);

        $l_res = $this->fetch($l_sql);

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_add_it = false;
            $l_data_id = null;

            if (isset($p_jdisc_to_idoit[$l_row['todeviceid']])) {
                $l_stack_member_object_id = $p_jdisc_to_idoit[$l_row['todeviceid']];
            } elseif ($l_stack_member_object_id = isys_cmdb_dao_category_g_identifier::get_object_id_by_identifer($l_row['todeviceid'])) {
                $this->m_log->debug('Found stack member device id in identifier cache.');
                $l_add_it = true;
            } elseif ($l_stack_member_object_id = isys_cmdb_dao_category_g_identifier::instance($this->get_database_component())
                ->get_object_id_by_key_value(
                    isys_cmdb_dao_category_g_identifier::get_identifier_type(),
                    isys_cmdb_dao_category_g_identifier::get_identifier_key(),
                    $l_row['todeviceid']
                )) {
                $this->m_log->debug('Found stack member device id in identifier from the database. Device id will be cached.');
                $l_add_it = true;
            } else {
                $this->m_log->debug('Stack member device with id ' . $l_row['todeviceid'] . ' has not been found in the system.');
                continue;
            }

            if ($l_stack_member_object_id) {
                // Skip it if its already assigned
                if (isset($l_stack_members[$l_stack_member_object_id])) {
                    $l_data_id = $l_stack_members[$l_stack_member_object_id];
                }

                // Add it to the Device 2 Object-ID mapping for the import
                if ($l_add_it) {
                    $p_jdisc_to_idoit[$l_row['todeviceid']] = $l_stack_member_object_id;
                }

                self::set_object_id($l_stack_member_object_id);

                $l_member_object_data = $l_dao->get_object_by_id($l_stack_member_object_id)
                    ->get_row();

                $l_return[] = [
                    'data_id'    => $l_data_id,
                    'properties' => [
                        'assigned_object' => [
                            'tag'        => 'assigned_object',
                            'value'      => $l_member_object_data['isys_obj__title'],
                            'id'         => $l_stack_member_object_id,
                            'type'       => $l_member_object_data['isys_obj__isys_obj_type__id'],
                            'sysid'      => $l_member_object_data['isys_obj__sysid'],
                            'title_lang' => $l_member_object_data['isys_obj_type__title'],
                            'title'      => 'LC__CATG__STACK_MEMBER__STACK_MEMBER'
                        ]
                    ]
                ];
            }
        }

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get('LC__CATG__STACK_MEMBER'),
                'const'             => 'C__CATG__STACK_MEMBER',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     *
     * @param   array $p_data
     *
     * @return  array
     */
    public function prepare_model($p_data)
    {
        $p_data['manufacturer'] = trim($p_data['manufacturer']);
        $p_data['model'] = trim($p_data['model']);

        $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', $p_data['manufacturer']);
        $l_title = isys_import_handler::check_dialog('isys_model_title', $p_data['model'], null, $l_manufacturer);

        return [
            C__DATA__TITLE      => $this->language->get('LC__CMDB__CATG__MODEL'),
            'const'             => 'C__CATG__MODEL',
            'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
            'category_entities' => [
                [
                    'data_id'    => null,
                    'properties' => [
                        'manufacturer' => [
                            'tag'        => 'manufacturer',
                            'value'      => $p_data['manufacturer'],
                            'id'         => $l_manufacturer,
                            'title_lang' => $p_data['manufacturer'],
                            'title'      => 'LC__CATG__STORAGE_MANUFACTURER',
                        ],
                        'title'        => [
                            'tag'        => 'title',
                            'value'      => $p_data['model'],
                            'id'         => $l_title,
                            'title_lang' => $p_data['model'],
                            'title'      => 'LC__CMDB__CATG__MODEL',
                        ],
                        'serial'       => [
                            'tag'        => 'serial',
                            'value'      => $p_data['serialnumber'],
                            'title_lang' => $p_data['serialnumber'],
                            'title'      => 'LC__CMDB__CATG__SERIAL',
                        ],
                        'productid'    => [
                            'tag'        => 'productid',
                            'value'      => $p_data['partnumber'],
                            'title_lang' => $p_data['partnumber'],
                            'title'      => 'LC__CMDB__CATG__MODEL_PRODUCTID',
                        ],
                        'firmware'     => [
                            'tag'        => 'firmware',
                            'value'      => $p_data['firmware'],
                            'title_lang' => $p_data['firmware'],
                            'title'      => 'LC__CMDB__CATG__FIRMWARE',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Prepare Method for category location
     *
     * @param $p_location_id
     *
     * @return array
     * @throws isys_exception_general
     */
    public function prepare_location($p_location_id = null)
    {
        $properties = null;
        if ($p_location_id) {
            if (!isset(self::$m_cached_locations[$p_location_id])) {
                $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
                $l_data = $l_dao->get_object_by_id($p_location_id)
                    ->get_row();
                self::$m_cached_locations[$p_location_id] = new SplFixedArray(5);
                self::$m_cached_locations[$p_location_id][0] = $l_data['isys_obj__id'];
                self::$m_cached_locations[$p_location_id][1] = $l_data['isys_obj__title'];
                self::$m_cached_locations[$p_location_id][2] = $l_data['isys_obj_type__const'];
                self::$m_cached_locations[$p_location_id][3] = $l_data['isys_obj_type__title'];
                self::$m_cached_locations[$p_location_id][4] = $l_data['isys_obj__sysid'];
            }

            $properties['parent'] = [
                'tag'      => 'parent',
                'value'    => self::$m_cached_locations[$p_location_id][0],
                'id'       => self::$m_cached_locations[$p_location_id][0],
                'type'     => self::$m_cached_locations[$p_location_id][2],
                'sysid'    => self::$m_cached_locations[$p_location_id][4],
                'lc_title' => self::$m_cached_locations[$p_location_id][3],
                'title'    => self::$m_cached_locations[$p_location_id][1],
            ];
        }

        return [
            C__DATA__TITLE      => $this->language->get('LC__CMDB__CATG__LOCATION'),
            'const'             => 'C__CATG__LOCATION',
            'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
            'category_entities' => [
                [
                    'data_id'    => null,
                    'properties' => $properties
                ]
            ]
        ];
    }

    /**
     * Prepares data for global category location with snmp syslocation
     *
     * @param $deviceId     int
     * @param $locationData []
     */
    public function prepareSnmpSysLocation($deviceId, $objectId, $locationData)
    {
        if (empty($locationData)) {
            $locationData = $this->prepare_location(null);
        }

        if ($deviceId > 0) {
            $pdoQuery = 'SELECT syslocation FROM devicebasicsnmpdata WHERE deviceid = ' . $this->convert_sql_id($deviceId);
            $result = $this->fetch($pdoQuery);

            // Its just one result
            $snmpData = $this->m_pdo->fetch_row_assoc($result);
            $locationData['category_entities'][0]['properties']['snmp_syslocation'] = [
                'tag'   => 'snmp_syslocation',
                'value' => $snmpData['syslocation']
            ];
        }

        if ($objectId) {
            // Get existing data
            $dao = isys_cmdb_dao_category_g_location::instance(isys_application::instance()->container->database);
            $existingData = $dao->get_data(null, $objectId)->get_row();

            $insertionMapping = [
                C__INSERTION__REAR => 'LC__CATG__LOCATION__BACKSIDE_OCCUPIED',
                C__INSERTION__FRONT => 'LC__CATG__LOCATION__FRONTSIDE_OCCUPIED',
                C__INSERTION__BOTH => 'LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED'
            ];

            $optionMapping = [
                C__RACK_INSERTION__HORIZONTAL => 'LC__CMDB__CATS__ENCLOSURE__HORIZONTAL',
                C__RACK_INSERTION__VERTICAL => 'LC__CMDB__CATS__ENCLOSURE__VERTICAL'
            ];

            $locationData['category_entities'][0]['properties'] = array_merge($locationData['category_entities'][0]['properties'], [
                'option' => [
                    'tag'        => 'option',
                    'value'      => $existingData['isys_catg_location_list__option'],
                    'title_lang' => $optionMapping[$existingData['isys_catg_location_list__option']] ?: '-',
                    'title'      => 'LC__CMDB__CATG__LOCATION_OPTION',
                ],
                'insertion' => [
                    'tag'        => 'insertion',
                    'value'      => (int)$existingData['isys_catg_location_list__insertion'],
                    'id'         => (int)$existingData['isys_catg_location_list__insertion'],
                    'title_lang' => $insertionMapping[$existingData['isys_catg_location_list__insertion']],
                    'title'      => 'LC__CMDB__CATG__LOCATION_FRONTSIDE',
                ],
                'pos' => [
                    'tag'        => 'pos',
                    'value'      => $existingData['isys_catg_location_list__pos'],
                    'title'      => 'LC__CMDB__CATG__LOCATION_POS',
                    'obj_id'     => $objectId
                ],
                'gps' => [
                    'tag'        => 'gps',
                    'value'      => $existingData['isys_catg_location_list__gps'],
                    'title'      => 'GPS'
                ],
                'description' => [
                    'tag' => 'description',
                    'value' => $existingData['isys_catg_location_list__description']
                ]
            ]);

            if ($existingData['isys_catg_location_list__parentid'] && !isset($locationData['category_entities'][0]['properties']['parent'])) {
                $parentData = $dao->get_object($existingData['isys_catg_location_list__parentid'])->get_row();
                $locationData['category_entities'][0]['properties']['parent'] = [
                    'tag'   => 'parent',
                    'value' => $parentData['isys_obj__id'],
                    'id' => $parentData['isys_obj__id'],
                    'type' => $parentData['isys_obj_type__const'],
                    'sysid' => $parentData['isys_obj__sysid'],
                    'lc_title' => $parentData['isys_obj_type__title'],
                    'title' => $parentData['isys_obj__title']
                ];
            }
        }

        return $locationData;
    }

    /**
     * Merges the template data with the import data
     *
     * @param array $p_current_object
     * @param array $p_template_object
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function merge_default_template(array &$p_current_object, array $p_template_object, array $blacklist)
    {
        if (count($p_template_object)) {
            foreach ([
                         C__CMDB__CATEGORY__TYPE_GLOBAL,
                         C__CMDB__CATEGORY__TYPE_SPECIFIC,
                         C__CMDB__CATEGORY__TYPE_CUSTOM
                     ] as $categorytype) {
                $categories = $p_template_object[$categorytype];

                if (!is_array($categories)) {
                    continue;
                }

                foreach ($categories as $categoryId => $categoryContent) {
                    if (isset($blacklist[$categorytype][$categoryId])) {
                        continue;
                    }

                    if ($categoryContent['const'] === 'C__CATG__GLOBAL') {
                        // Merge Global category
                        foreach ($categoryContent['category_entities'][0]['properties'] as $key => $value) {
                            if (isset($p_current_object[$key])) {
                                if ($key == 'tag') {
                                    continue;
                                } elseif ($key == 'type') {
                                    $value[C__DATA__VALUE] = $p_current_object[$key]['id'];
                                } else {
                                    $value[C__DATA__VALUE] = $p_current_object[$key];
                                }
                                $categoryContent['category_entities'][0]['properties'][$key] = $value;
                            }
                        }
                    }
                    $p_current_object['categories'][$categorytype . '_' . $categoryId] = $categoryContent;
                }
            }
        }
    }

    public function __construct(isys_component_database $p_db, isys_component_database_pdo $p_pdo)
    {
        $this->m_module_objecttype = defined_or_default('C__OBJTYPE__SWITCH');
        parent::__construct($p_db, $p_pdo);
    }
}
