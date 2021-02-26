<?php

namespace idoit\Console\Command\Import\Ocs;

use isys_application;
use isys_component_dao_ocs;
use idoit\Component\Helper\Ip;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ModelSerial;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ObjectTitle;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Mac;
use isys_cmdb_dao_category_g_ip;
use isys_cmdb_dao_category_g_network_port;
use isys_cmdb_dao_category_g_network_interface;
use isys_cmdb_dao_category_g_power_consumer;
use isys_cmdb_dao_category_s_net;
use isys_cmdb_dao_category_g_model;
use isys_cmdb_dao_category_g_stack_member;
use isys_cmdb_dao_category_g_cpu;
use isys_cmdb_dao_cable_connection;
use isys_cmdb_dao_category_g_memory;
use isys_cmdb_dao_category_g_ui;
use isys_cmdb_dao_category_g_stor;
use isys_cmdb_dao_category_g_application;
use isys_cmdb_dao_category_s_application;
use isys_cmdb_dao_category_g_relation;
use isys_cmdb_dao_category_g_version;
use isys_event_manager;
use isys_import_handler_cmdb;
use isys_import_handler;
use isys_cmdb_dao_dialog;
use isys_convert;
use SplFixedArray;
use isys_tenantsettings;
use isys_cmdb_dao_connection;

class Snmp extends AbstractOcs
{
    /**
     * @var array
     */
    private $alreadyImportedDevices = [];

    /**
     * @param isys_component_dao_ocs $p_dao
     * @param null                   $p_snmp_id
     * @param bool                   $p_add_memory
     * @param bool                   $p_add_storage
     * @param bool                   $p_add_net
     * @param                        $p_add_cpu
     * @param bool                   $p_add_ui
     * @param bool                   $p_add_model
     * @param bool                   $p_add_application
     * @param bool                   $p_add_graphic
     * @param bool                   $p_add_sound
     * @param bool                   $p_add_drive
     * @param bool                   $p_add_vm
     *
     * @return array
     */
    public function getDeviceInfo(
        $p_dao,
        $p_snmp_id = null,
        $p_add_memory = false,
        $p_add_storage = false,
        $p_add_net = false,
        $p_add_cpu,
        $p_add_ui = false,
        $p_add_model = false,
        $p_add_application = false,
        $p_add_graphic = false,
        $p_add_sound = false,
        $p_add_drive = false,
        $p_add_vm = false
    ) {
        $l_data = [];

        if ($p_dao->does_snmp_exist()) {
            $l_memory = [];
            $l_app = [];
            $l_graphic = [];
            $l_sound = [];
            $l_drive = [];
            $l_net = [];
            $l_ui = [];
            $l_stor = [];
            $l_cpu = [];

            /**
             * @var $p_dao isys_component_dao_ocs
             */
            $l_data = [
                'inventory' => $p_dao->getHardwareItemBySNMP($p_snmp_id)
            ];

            if (isset($this->alreadyImportedDevices[trim($l_data['inventory']['NAME'])])) {
                return false;
            }

            $this->alreadyImportedDevices[trim($l_data['inventory']['NAME'])] = true;
            $l_data['inventory']['macaddr'] = $p_dao->get_unique_mac_addresses($p_snmp_id, true);

            if ($p_add_cpu) {
                $l_res_ocs_cpu = $p_dao->getCPU($p_snmp_id, true);
                while ($l_row = $l_res_ocs_cpu->get_row()) {
                    $l_speed = $l_row['SPEED'];
                    $l_row['SPEED'] = preg_replace('/[^0-9.]*/', '', $l_speed);
                    $l_row['UNIT'] = preg_replace('/[0-9.]*/', '', $l_speed);
                    $l_cpu[] = $l_row;
                }
                $l_data['cpu'] = $l_cpu;
            }

            if ($p_add_memory) {
                $l_res_ocs_memory = $p_dao->getMemory($p_snmp_id, true);
                while ($l_row = $l_res_ocs_memory->get_row()) {
                    $l_memory[] = $l_row;
                }
                $l_data['memory'] = $l_memory;
            }

            if ($p_add_net) {
                $l_res_net = $p_dao->getNetworkAdapter($p_snmp_id, true);
                $l_subnetmasks = [];
                $l_counter = 0;
                $l_addresses = [];
                $l_primary_set = false;
                while ($l_row = $l_res_net->get_row()) {
                    if ($l_row['DEVICENAME'] !== '' && $l_row['DEVICEPORT'] !== '') {
                        $l_connected_to = $p_dao->getNetworkConnectedTo($l_row['DEVICENAME'], $l_row['DEVICEPORT']);
                        if (is_array($l_connected_to)) {
                            $l_row += $l_connected_to;
                        }
                    }
                    $l_net[$l_counter] = $l_row;
                    $l_counter++;

                    if ($l_row['IPADDR'] !== '') {
                        $l_addresses[$l_row['IPADDR']] = true;
                        $l_row['PRIMARY'] = ($l_primary_set === false) ? true : false;
                        $l_primary_set = true;
                    } else {
                        continue;
                    }

                    $l_subnetmasks[Ip::validate_net_ip($l_row['IPADDR'], $l_row['IPMASK'], null, true)] = $l_row['IPMASK'];
                }
                if ($l_data['inventory']['IPADDR'] != '' && !isset($l_addresses[$l_data['inventory']['IPADDR']])) {
                    $l_subnetmask = '255.255.255.0';
                    if (count($l_subnetmasks) > 0) {
                        $l_cache_net_ip_arr = explode('.', $l_data['inventory']['IPADDR']);
                        $l_cache_net_ip_first = $l_cache_net_ip_arr[0] . '.' . $l_cache_net_ip_arr[1] . '.' . $l_cache_net_ip_arr[2] . '.0';
                        $l_cache_net_ip_second = $l_cache_net_ip_arr[0] . '.' . $l_cache_net_ip_arr[1] . '.0.0';
                        $l_cache_net_ip_third = $l_cache_net_ip_arr[0] . '.0.0.0';
                        if (isset($l_subnetmasks[$l_cache_net_ip_first])) {
                            $l_subnetmask = $l_subnetmasks[$l_cache_net_ip_first];
                        } elseif (isset($l_subnetmasks[$l_cache_net_ip_second])) {
                            $l_subnetmask = $l_subnetmasks[$l_cache_net_ip_second];
                        } elseif (isset($l_subnetmasks[$l_cache_net_ip_third])) {
                            $l_subnetmask = $l_subnetmasks[$l_cache_net_ip_third];
                        }
                    }

                    $l_net[] = [
                        'IPADDR'   => $l_data['inventory']['IPADDR'],
                        'IPMASK'   => $l_subnetmask,
                        'IPSUBNET' => Ip::validate_net_ip($l_data['inventory']['IPADDR'], $l_subnetmask, null, true),
                        'STATUS'   => 'Up',
                        'PRIMARY'  => ($l_primary_set === false) ? true : false
                    ];
                }

                $l_res_interface = $p_dao->getSNMPNetworkInterfaces($p_snmp_id);
                while ($l_row = $l_res_interface->get_row()) {
                    $l_net['interfaces'][] = $l_row;
                }

                $l_res_ps = $p_dao->getSNMPPowerSupplies($p_snmp_id);
                while ($l_row = $l_res_ps->get_row()) {
                    $l_net['powersupplies'][] = $l_row;
                }

                $l_data['net'] = $l_net;
            }

            if ($p_add_storage) {
                $l_res_stor = $p_dao->getStorage($p_snmp_id, true);
                while ($l_row = $l_res_stor->get_row()) {
                    $l_stor[] = $l_row;
                }
                $l_data['stor'] = $l_stor;
            }

            if ($p_add_ui) {
                $l_res_ui = $p_dao->getPorts($p_snmp_id, true);
                while ($l_row = $l_res_ui->get_row()) {
                    $l_ui[] = $l_row;
                }
                $l_data['ui'] = $l_ui;
            }

            if ($p_add_model) {
                $l_data['model'] = $p_dao->getBios($p_snmp_id, true)
                    ->__as_array();
            }

            if ($p_add_application) {
                $l_res_software = $p_dao->getSoftware($p_snmp_id, true);
                while ($l_row = $l_res_software->get_row()) {
                    $l_app[] = $l_row;
                }
                $l_data['application'] = $l_app;
            }
        }

        return $l_data;
    }

    /**
     * @param      $objId
     * @param      $objType
     * @param      $hardwareModelData
     * @param      $deviceTitle
     * @param      $modelManufactuerDefault
     * @param bool $logbookActive
     *
     * @throws \Exception
     * @throws \isys_exception_cmdb
     * @throws \isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleModel($objId, $objType, $hardwareModelData, $deviceTitle, $modelManufactuerDefault, $logbookActive = false)
    {
        $l_data = [];
        $daoModel = isys_cmdb_dao_category_g_model::instance($this->container->database);
        $daoStackMember = isys_cmdb_dao_category_g_stack_member::instance($this->container->database);

        $l_rowModel = null;

        if (is_countable($hardwareModelData) && count($hardwareModelData) > 1) {
            // Its a chassis
            $l_assigned_switch_stack = [];
            foreach ($hardwareModelData as $l_switch_stack) {
                $l_obj_id_switch_stack = $this->getMatcher()
                    ->get_object_id_by_matching(
                        $l_switch_stack['SERIALNUMBER'],
                        [],
                        $deviceTitle . ' - ' . $l_switch_stack['TITLE'],
                        null,
                        (ModelSerial::getBit() + ObjectTitle::getBit()),
                        2
                    );
                $l_import_mode = isys_import_handler_cmdb::C__UPDATE;

                if (!$l_obj_id_switch_stack && defined('C__OBJTYPE__SWITCH')) {
                    $l_obj_id_switch_stack = $daoModel->insert_new_obj(
                        C__OBJTYPE__SWITCH,
                        null,
                        $deviceTitle . ' - ' . $l_switch_stack['TITLE'],
                        null,
                        C__RECORD_STATUS__NORMAL
                    );
                    $l_import_mode = isys_import_handler_cmdb::C__CREATE;
                }

                $l_objModelData = $daoModel->get_data(null, $l_obj_id_switch_stack)
                    ->get_row() ?: null;

                $l_data['manufacturer'] = $l_switch_stack["MANUFACTURER"] = (trim($l_switch_stack["MANUFACTURER"]) ==
                '' ? $modelManufactuerDefault : trim($l_switch_stack["MANUFACTURER"]));
                $l_data['serial'] = $l_switch_stack["SERIALNUMBER"];
                $l_data['title'] = $l_switch_stack["DESCRIPTION"];
                $l_data['firmware'] = ($l_switch_stack["FIRMVERSION"] != '') ? $l_switch_stack["FIRMVERSION"] : $l_switch_stack["FIRMVERSION_IOS"];
                $l_data['data_id'] = $l_objModelData['isys_catg_model_list__id'] ?: null;

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'manufacturer' => ['title_lang' => $l_switch_stack["MANUFACTURER"]],
                        'title'        => ['title_lang' => $l_switch_stack["DESCRIPTION"]],
                        'serial'       => ['value' => $l_switch_stack["SERIALNUMBER"]],
                        'firmware'     => [
                            'value' => (($l_switch_stack["FIRMVERSION"] != '') ? $l_switch_stack["FIRMVERSION"] : $l_switch_stack["FIRMVERSION_IOS"])
                        ]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($daoModel, $l_objModelData, $l_category_values);
                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent(
                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                "-modified from OCS-",
                                $l_obj_id_switch_stack,
                                defined_or_default('C__OBJTYPE__SWITCH'),
                                'LC__CMDB__CATG__MODEL',
                                serialize($l_changes)
                            );
                    }
                }
                $l_import_data = $daoModel->parse_import_array($l_data);
                $daoModel->sync($l_import_data, $l_obj_id_switch_stack, $l_import_mode);
                $l_assigned_switch_stack[$l_obj_id_switch_stack] = $l_obj_id_switch_stack;
            }

            $l_stackMemberResult = $daoStackMember->get_connected_objects($objId);
            if (is_countable($l_stackMemberResult) && count($l_stackMemberResult) > 0) {
                while ($l_stackMember = $l_stackMemberResult->get_row()) {
                    if (isset($l_assigned_switch_stack[$l_stackMember['isys_catg_stack_member_list__stack_member']])) {
                        unset($l_assigned_switch_stack[$l_stackMember['isys_catg_stack_member_list__stack_member']]);
                    }
                }
            }

            if (is_countable($l_assigned_switch_stack) && count($l_assigned_switch_stack) > 0) {
                foreach ($l_assigned_switch_stack as $l_newStackMember) {
                    $daoStackMember->create_data(['isys_obj__id' => $objId, 'assigned_object' => $l_newStackMember, 'mode' => null]);
                }
            }
        } else {
            $l_res = $daoModel->get_data(null, $objId);
            if ($l_res->num_rows() < 1) {
                $l_data['description'] = null;
            } else {
                $l_rowModel = $l_res->get_row();
                $l_data['description'] = $l_rowModel["isys_catg_model_list__description"];
                $l_data['data_id'] = $l_rowModel['isys_catg_model_list__id'];
            }
            $l_data['manufacturer'] = $hardwareModelData[0]["MANUFACTURER"] = (trim($hardwareModelData[0]["MANUFACTURER"]) ==
            '' ? $modelManufactuerDefault : trim($hardwareModelData[0]["MANUFACTURER"]));
            $l_data['title'] = $hardwareModelData[0]['DESCRIPTION'];
            $l_data['serial'] = $hardwareModelData[0]["SERIALNUMBER"];
            $l_data['firmware'] = ($hardwareModelData[0]["FIRMVERSION"] !== '') ? $hardwareModelData[0]["FIRMVERSION"] : $hardwareModelData[0]['FIRMVERSION_IOS'];

            if ($logbookActive) {
                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                    'manufacturer' => ['title_lang' => $hardwareModelData[0]["MANUFACTURER"]],
                    'title'        => ['title_lang' => $hardwareModelData[0]["DESCRIPTION"]],
                    'serial'       => ['value' => $hardwareModelData[0]["SERIALNUMBER"]],
                    'firmware'     => [
                        'value' => (($hardwareModelData[0]["FIRMVERSION"] !== '') ? $hardwareModelData[0]["FIRMVERSION"] : $hardwareModelData[0]['FIRMVERSION_IOS'])
                    ]
                ];
                $l_changes = $this->getLogbook()
                    ->prepare_changes($daoModel, $l_rowModel, $l_category_values);

                if (is_countable($l_changes) && count($l_changes) > 0) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__MODEL', serialize($l_changes));
                }
            }
            $l_import_data = $daoModel->parse_import_array($l_data);
            $l_entry_id = $daoModel->sync($l_import_data, $objId, ((!empty($l_data['data_id'])) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE));

            // Emit category signal (afterCategoryEntrySave).
            $this->container->signals->emit(
                "mod.cmdb.afterCategoryEntrySave",
                $daoModel,
                $l_entry_id,
                !!$l_entry_id,
                $objId,
                $l_import_data,
                isset($l_changes) ? $l_changes : []
            );
        }
    }

    /**
     * @param      $objId
     * @param      $objType
     * @param      $cpuData
     * @param      $frequencyUnit
     * @param bool $logbookActive
     *
     * @throws \Exception
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleCpu($objId, $objType, $cpuData, $frequencyUnit, $logbookActive = false, $cores = 1)
    {
        $l_check_data = [];

        $l_daoCPU = isys_cmdb_dao_category_g_cpu::instance($this->container->database);
        $l_res = $l_daoCPU->get_data(null, $objId);
        $l_cpus = $l_res->num_rows();

        if ($l_cpus > 0) {
            // Get data in i-doit
            while ($l_rowCPU = $l_res->get_row()) {
                $l_check_data[] = [
                    'data_id'        => $l_rowCPU['isys_catg_cpu_list__id'],
                    'title'          => $l_rowCPU["isys_catg_cpu_list__title"],
                    'manufacturer'   => $l_rowCPU['isys_catg_cpu_manufacturer__title'],
                    'type'           => $l_rowCPU['isys_catg_cpu_type__title'],
                    'frequency'      => $l_rowCPU['isys_catg_cpu_list__frequency'],
                    'frequency_unit' => $l_rowCPU['isys_catg_cpu_list__isys_frequency_unit__id'],
                    'cores'          => $l_rowCPU['isys_catg_cpu_list__cores'],
                    'description'    => $l_rowCPU['isys_catg_cpu_list__description']
                ];
                $l_rowCPU_arr[$l_rowCPU['isys_catg_cpu_list__id']] = $l_rowCPU;
            }

            if (is_countable($cpuData) && count($cpuData) > 0) {
                foreach ($cpuData as $l_rowCPU) {
                    // Check data from ocs with data from i-doit
                    foreach ($l_check_data as $l_key => $l_val) {
                        $convertedFrequency = isys_convert::frequency($l_rowCPU['SPEED'], $frequencyUnit);

                        if ($l_val['type'] == $l_rowCPU['TYPE'] && (String)$l_val['manufacturer'] == $l_rowCPU['MANUFACTURER'] &&
                            (int)$l_val['frequency'] == (int)$convertedFrequency) {
                            unset($l_check_data[$l_key]);
                            continue 2;
                        }
                    }

                    // Raw array for preparing the import array
                    $l_data = [
                        'title'        => isset($l_rowCPU['CORES']) ? $l_rowCPU['TYPE'] : null,
                        'manufacturer' => $l_rowCPU['MANUFACTURER'],
                        'type'         => isset($l_rowCPU['CORES']) ? null : $l_rowCPU['TYPE'],
                        'frequency_unit' => $frequencyUnit,
                        'cores'          => (isset($l_rowCPU['CORES']) ? $l_rowCPU['CORES'] : $cores),
                        'frequency'      => $l_rowCPU['SPEED']
                    ];

                    if ($logbookActive) {
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                            'manufacturer'   => ['title_lang' => $l_rowCPU['MANUFACTURER']],
                            'frequency'      => ['value' => $l_rowCPU['SPEED']],
                            'type'           => isset($l_rowCPU['CORES']) ? null : ['title_lang' => $l_rowCPU['TYPE']],
                            'frequency_unit' => ['title_lang' => $l_rowCPU['UNIT']]
                        ];

                        $l_changes = $this->getLogbook()
                            ->prepare_changes($l_daoCPU, null, $l_category_values);
                        if (is_countable($l_changes) && count($l_changes) > 0) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__CMDB__CATG__CPU',
                                    serialize($l_changes)
                                );
                        }
                    }

                    $l_import_data = $l_daoCPU->parse_import_array($l_data);
                    $l_entry_id = $l_daoCPU->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                    // Emit category signal (afterCategoryEntrySave).
                    $this->container->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $l_daoCPU,
                        $l_entry_id,
                        !!$l_entry_id,
                        $objId,
                        $l_import_data,
                        isset($l_changes) ? $l_changes : []
                    );
                }
            }
            // Delete entries
            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $l_daoCPU, 'isys_catg_cpu_list', $objId, $objType, 'LC__CMDB__CATG__CPU', $logbookActive);
            }
        } else {
            // Create
            foreach ($cpuData as $l_rowCPU) {
                $l_data = [
                    'title'          => isset($l_rowCPU['CORES']) ? $l_rowCPU['TYPE']: null,
                    'manufacturer'   => $l_rowCPU['MANUFACTURER'],
                    'type'           => isset($l_rowCPU['CORES']) ? null : $l_rowCPU['TYPE'],
                    'frequency_unit' => $frequencyUnit,
                    'cores'          => (isset($l_rowCPU['CORES']) ? $l_rowCPU['CORES'] : $cores),
                    'frequency'      => $l_rowCPU['SPEED']
                ];
                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'          => isset($l_rowCPU['CORES']) ? ['value' => $l_rowCPU['TYPE']]: null,
                        'frequency'      => ['value' => $l_rowCPU['SPEED']],
                        'frequency_unit' => ['title_lang' => 'GHz'],
                        'type'           => isset($l_rowCPU['CORES']) ? null : ['title_lang' => $l_rowCPU['TYPE']]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($l_daoCPU, null, $l_category_values);
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__CPU', serialize($l_changes));
                }

                $l_import_data = $l_daoCPU->parse_import_array($l_data);
                $l_entry_id = $l_daoCPU->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $l_daoCPU,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }
        }
    }

    /**
     * Memories via SNMP are discovered in KB
     *
     * @param int   $objId
     * @param int   $objType
     * @param array $memoryData
     * @param int   $memoryUnit
     * @param bool  $logbookActive
     *
     * @throws \Exception
     * @throws \isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleMemory($objId, $objType, $memoryData, $memoryUnit, $logbookActive = false)
    {
        $l_daoMemory = isys_cmdb_dao_category_g_memory::instance($this->container->database);

        $l_check_data = [];
        $l_res = $l_daoMemory->get_data(null, $objId);
        $l_mem_amount = $l_res->num_rows();
        if ($l_mem_amount > 0) {
            // Get data in i-doit
            while ($l_rowMemory = $l_res->get_row()) {
                $l_check_data[] = [
                    'data_id'      => $l_rowMemory['isys_catg_memory_list__id'],
                    'title'        => $l_rowMemory["isys_memory_title__title"],
                    'manufacturer' => $l_rowMemory['isys_memory_manufacturer__title'],
                    'type'         => $l_rowMemory['isys_memory_type__title'],
                    'unit'         => $l_rowMemory['isys_catg_memory_list__isys_memory_unit__id'],
                    'capacity'     => $l_rowMemory['isys_catg_memory_list__capacity'],
                    'description'  => $l_rowMemory['isys_catg_memory_list__description']
                ];
            }

            if (is_countable($memoryData) && count($memoryData) > 0) {
                foreach ($memoryData as $l_rowMemory) {

                    // Byte
                    $convertedMemory = isys_convert::memory($l_rowMemory['CAPACITY'], $memoryUnit, C__CONVERT_DIRECTION__FORMWARD);
                    $newMemoryUnit = isys_convert::get_memory_unit_const($convertedMemory, true);
                    $memoryUnitTitle = str_replace('C__MEMORY_UNIT__', '', $newMemoryUnit);
                    $memoryUnitId = isys_convert::get_memory_unit_const($convertedMemory);
                    $l_rowMemory['CAPACITY'] = isys_convert::memory($convertedMemory, $newMemoryUnit, C__CONVERT_DIRECTION__BACKWARD);

                    // Check data from ocs with data from i-doit
                    foreach ($l_check_data as $l_key => $l_val) {
                        if ((float)$l_val['capacity'] === $convertedMemory) {
                            unset($l_check_data[$l_key]);
                            continue 2;
                        }
                    }

                    // Raw array for preparing the import array
                    $l_data = [
                        'title'        => null,
                        'manufacturer' => null,
                        'type'         => null,
                        'unit'         => $memoryUnitId,
                        'capacity'     => $l_rowMemory['CAPACITY']
                    ];

                    if ($logbookActive) {
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                            'unit'     => ['title_lang' => $memoryUnitTitle],
                            'capacity' => ['value' => $l_rowMemory["CAPACITY"]]
                        ];

                        $l_changes = $this->getLogbook()
                            ->prepare_changes($l_daoMemory, null, $l_category_values);
                        if (is_countable($l_changes) && count($l_changes) > 0) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__CMDB__CATG__MEMORY',
                                    serialize($l_changes)
                                );
                        }
                    }

                    $l_import_data = $l_daoMemory->parse_import_array($l_data);
                    $l_entry_id = $l_daoMemory->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                    // Emit category signal (afterCategoryEntrySave).
                    $this->container->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $l_daoMemory,
                        $l_entry_id,
                        !!$l_entry_id,
                        $objId,
                        $l_import_data,
                        isset($l_changes) ? $l_changes : []
                    );
                }
            }
            // Delete entries
            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $l_daoMemory, 'isys_catg_memory_list', $objId, $objType, 'LC__CMDB__CATG__MEMORY', $logbookActive);
            }
        } else {
            // Create entries
            foreach ($memoryData as $l_rowMemory) {
                // Byte
                $convertedMemory = isys_convert::memory($l_rowMemory['CAPACITY'], $memoryUnit, C__CONVERT_DIRECTION__FORMWARD);
                $newMemoryUnit = isys_convert::get_memory_unit_const($convertedMemory, true);
                $memoryUnitTitle = str_replace('C__MEMORY_UNIT__', '', $newMemoryUnit);
                $memoryUnitId = isys_convert::get_memory_unit_const($convertedMemory);
                $l_rowMemory['CAPACITY'] = isys_convert::memory($convertedMemory, $newMemoryUnit, C__CONVERT_DIRECTION__BACKWARD);

                $l_data = [
                    'title'        => null,
                    'manufacturer' => null,
                    'type'         => null,
                    'unit'         => $memoryUnitId,
                    'capacity'     => $l_rowMemory["CAPACITY"]
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'unit'     => ['title_lang' => $memoryUnitTitle],
                        'capacity' => ['value' => $l_rowMemory["CAPACITY"]]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($l_daoMemory, null, $l_category_values);
                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__MEMORY', serialize($l_changes));
                    }
                }

                $l_import_data = $l_daoMemory->parse_import_array($l_data);
                $l_entry_id = $l_daoMemory->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $l_daoMemory,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }
        }
    }

    /**
     *
     * Mehod which handles the network (port, interfaces, ip and power consumers for snmp devices
     *
     * @param       $objId
     * @param       $objType
     * @param array $netData
     * @param       $deviceTitle
     * @param       $availableNets
     * @param       $portDescriptions
     * @param       $portConnections
     * @param bool  $logbookActive
     * @param bool  $overwriteIpPorts
     *
     * @throws \Exception
     * @throws \isys_exception_cmdb
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleNet(
        $objId,
        $objType,
        array $netData,
        $deviceTitle,
        &$availableNets,
        $logbookActive = false,
        $overwriteIpPorts = false
    ) {
        $ipDao = isys_cmdb_dao_category_g_ip::instance($this->container->database);
        $portDao = isys_cmdb_dao_category_g_network_port::instance($this->container->database);
        $interfaceDao = isys_cmdb_dao_category_g_network_interface::instance($this->container->database);
        $powerConsumerDao = isys_cmdb_dao_category_g_power_consumer::instance($this->container->database);
        $cableConDao = isys_cmdb_dao_cable_connection::instance($this->container->database);

        $portConnections = $portDescriptions = $l_check_ip = $l_check_net = $l_check_iface = $l_check_port = $l_already_imported_ports = $l_already_imported_ips = [];
        // IP info
        $l_query_ip = 'SELECT t1.isys_catg_ip_list__id, t1.isys_catg_ip_list__hostname, t2.isys_cats_net_ip_addresses_list__title, t3.* ' . 'FROM isys_catg_ip_list  AS t1 ' .
            'INNER JOIN isys_cats_net_ip_addresses_list AS t2 ON t2.isys_cats_net_ip_addresses_list__id = t1.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id ' .
            'INNER JOIN isys_cats_net_list AS t3 ON t3.isys_cats_net_list__isys_obj__id = t2.isys_cats_net_ip_addresses_list__isys_obj__id ' .
            'WHERE t1.isys_catg_ip_list__isys_obj__id = ' . $ipDao->convert_sql_id($objId);
        $l_res_ip = $ipDao->retrieve($l_query_ip);
        while ($l_row_ip = $l_res_ip->get_row()) {
            $l_check_ip[] = [
                'data_id'   => $l_row_ip['isys_catg_ip_list__id'],
                'title'     => $l_row_ip['isys_cats_net_ip_addresses_list__title'],
                'net'       => $l_row_ip['isys_cats_net_list__isys_obj__id'],
                'net_title' => $l_row_ip['isys_cats_net_list__address'],
                'hostnmae'  => $l_row_ip['isys_catg_ip_list__hostname'],
                'primary'   => (bool)$l_row_ip['isys_catg_ip_list__primary']
            ];
            $l_check_net[] = [
                'data_id'    => $l_row_ip['isys_cats_net_list__id'],
                'title'      => $l_row_ip['isys_cats_net_list__address'],
                'mask'       => $l_row_ip['isys_cats_net_list__mask'],
                'range_from' => $l_row_ip['isys_cats_net_list__address_range_from'],
                'range_to'   => $l_row_ip['isys_cats_net_list__address_range_to'],
            ];
        }

        // Port info
        $l_query_port = 'SELECT * FROM isys_catg_port_list ' .
            'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id ' .
            'LEFT JOIN isys_catg_netp_list ON isys_catg_netp_list__id = isys_catg_port_list__isys_catg_netp_list__id ' .
            'LEFT JOIN isys_port_type ON isys_port_type__id = isys_catg_port_list__isys_port_type__id ' .
            'LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id ' . 'WHERE isys_catg_port_list__isys_obj__id = ' .
            $portDao->convert_sql_id($objId);
        $l_res_port = $portDao->retrieve($l_query_port);
        while ($l_row_port = $l_res_port->get_row()) {
            $l_check_port[] = [
                'data_id'            => $l_row_port['isys_catg_port_list__id'],
                'title'              => $l_row_port['isys_catg_port_list__title'],
                'mac'                => $l_row_port['isys_catg_port_list__mac'],
                'speed'              => $l_row_port['isys_catg_port_list__port_speed_value'],
                'speed_type'         => $l_row_port['isys_port_speed__id'],
                'port_type'          => $l_row_port['isys_port_type__title'],
                'active'             => $l_row_port['isys_catg_port_list__state_enabled'],
                'assigned_connector' => $l_row_port['isys_catg_port_list__isys_catg_connector_list__id'],
                'description'        => $l_row_port['isys_catg_port_list__description']
            ];
        }

        // Interface info
        $l_check_iface = [];
        $l_existing_ifaces = [];

        $l_query_interface = 'SELECT isys_catg_netp_list__id, isys_catg_netp_list__title FROM isys_catg_netp_list ' . 'WHERE isys_catg_netp_list__isys_obj__id = ' .
            $interfaceDao->convert_sql_id($objId);
        $l_res_iface = $interfaceDao->retrieve($l_query_interface);
        while ($l_row_iface = $l_res_iface->get_row()) {
            $l_check_iface[] = [
                'data_id' => $l_row_iface['isys_catg_netp_list__id'],
                'title'   => $l_row_iface['isys_catg_netp_list__title']
            ];
        }

        $l_check_ps = [];
        $l_existing_ps = [];
        $l_query_ps = 'SELECT isys_catg_pc_list__id, isys_catg_pc_list__title FROM isys_catg_pc_list
							WHERE isys_catg_pc_list__isys_obj__id = ' . $powerConsumerDao->convert_sql_id($objId);
        $l_res_ps = $powerConsumerDao->retrieve($l_query_ps);
        while ($l_row_ps = $l_res_ps->get_row()) {
            $l_check_ps[] = [
                'data_id' => $l_row_ps['isys_catg_pc_list__id'],
                'title'   => $l_row_ps['isys_catg_pc_list__title']
            ];
        }

        // Copy list of existing ifaces
        $l_existing_ifaces = $l_check_iface;
        $l_existing_ps = $l_check_ps;

        $l_counter = 0;
        $l_net_amount = count($netData) - 1;

        foreach ($netData as $l_hw_key => $l_row) {
            $l_speed = null;
            $l_speed_type_id = null;
            if (isset($l_row['SPEED']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies') {
                preg_match('/[0-9]*\.[0-9]*|[0-9]*/', $l_row['SPEED'], $l_speed_arr);
                $l_speed = $l_speed_arr[0];
                $l_speed_type = ltrim(str_replace($l_speed, '', $l_row['SPEED']));
                $l_speed_type_lower = strtolower($l_speed_type);

                if ($l_speed_type_lower == 'mb/s' || $l_speed_type_lower == 'm' || $l_speed_type_lower == 'mb' || $l_speed_type_lower == 'mbps') {
                    $l_speed_type_id = defined_or_default('C__PORT_SPEED__MBIT_S', 3);
                } elseif ($l_speed_type_lower == 'gb/s' || $l_speed_type_lower == 'g' || $l_speed_type_lower == 'gb' || $l_speed_type_lower == 'gbps') {
                    $l_speed_type_id = defined_or_default('C__PORT_SPEED__GBIT_S', 4);
                }
            }

            $l_address = null;
            $l_interface_id = null;
            $l_port_id = null;
            $l_ip_id = null;
            $l_sync_ip = true;
            $l_sync_port = true;
            $l_sync_iface = true;

            // Trim iface title if needed
            if (isset($l_row['SLOT']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies') {
                $l_row['SLOT'] = trim($l_row['SLOT']);
            }

            if (count($l_existing_ifaces) > 0 && $l_hw_key === 'interfaces') {
                foreach ($l_row as $l_interface_key => $l_interface_data) {
                    foreach ($l_existing_ifaces as $l_key => $l_iface) {
                        if (strcasecmp($l_iface['title'], $l_interface_data['REFERENCE']) == 0 && $l_iface['serial'] === $l_interface_data['SERIALNUMBER']) {
                            $l_interface_id = $l_existing_ifaces[$l_key]['data_id'];
                            $l_row[$l_interface_key] = null;
                            // Unset to prevent removing of iface later
                            if (isset($l_check_iface[$l_key])) {
                                unset($l_check_iface[$l_key]);
                            }
                            continue;
                        }
                    }
                }
            }
            if (count($l_existing_ps) > 0 && $l_hw_key === 'powersupplies') {
                foreach ($l_row as $l_ps_key => $l_ps_data) {
                    foreach ($l_existing_ps as $l_key => $l_ps) {
                        if (strcasecmp($l_ps['title'], $l_ps_data['TITLE']) == 0) {
                            $l_ps_id = $l_existing_ps[$l_key]['data_id'];
                            $l_row[$l_ps_key] = null;
                            // Unset to prevent removing of iface later
                            if (isset($l_check_ps[$l_key])) {
                                unset($l_check_ps[$l_key]);
                            }
                            continue;
                        }
                    }
                }
            }

            if (count($l_check_port) > 0 && isset($l_row['TYPE']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies') {
                foreach ($l_check_port as $l_key => $l_port) {
                    if ($l_port['title'] == $l_row['SLOT'] && strcmp($l_port['mac'], $l_row['MACADDR']) == 0) {
                        $l_port_id = $l_check_port[$l_key]['data_id'];
                        unset($l_check_port[$l_key]);
                        $l_arr = new SplFixedArray(2);
                        $l_arr[0] = $l_port['data_id'];
                        $l_arr[1] = $l_port['description'];
                        $l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']] = $l_arr;
                        $l_sync_port = false;

                        if (empty($l_port['description']) && $l_port['description'] != $l_row['DESCRIPTION'] && !isset($portDescriptions[$l_port_id]) &&
                            $l_port['description'] === '') {
                            $portDescriptions[$l_port_id] = $l_row['DESCRIPTION'];
                        }
                        continue;
                    }
                }
            }

            if (count($l_check_ip) > 0 && isset($l_row['IPADDR']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies') {
                foreach ($l_check_ip as $l_key => $l_ip) {
                    if ($l_ip['title'] == $l_row['IPADDR']) {
                        if (!!$l_row['PRIMARY'] === true) {
                            // Check hostname
                            if ($l_ip['hostname'] != substr($deviceTitle, 0, strpos($deviceTitle, '.'))) {
                                continue;
                            }
                        }

                        $l_ip_id = $l_check_ip[$l_key]['data_id'];
                        unset($l_check_ip[$l_key]);
                        unset($l_check_net[$l_key]);
                        $l_already_imported_ips[$l_ip_id] = $l_ip['title'];
                        $l_sync_ip = false;
                        continue;
                    }
                }
            }

            // Sync Power Consumer
            if ($l_hw_key === 'powersupplies') {
                foreach ($l_row as $l_ps_data) {
                    if ($l_ps_data === null) {
                        continue;
                    }

                    $l_data = [
                        'title'        => $l_ps_data['TITLE'],
                        'manufacturer' => $l_ps_data['MANUFACTURER']
                    ];

                    if ($logbookActive) {
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                            'title'        => ['value' => $l_ps_data['TITLE']],
                            'manufacturer' => ['title_lang' => $l_ps_data["MANUFACTURER"]]
                        ];
                        $l_changes = $this->getLogbook()
                            ->prepare_changes($powerConsumerDao, null, $l_category_values);
                        if (is_countable($l_changes) && count($l_changes) > 0) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                    serialize($l_changes)
                                );
                        }
                    }

                    if (!empty($l_data['manufacturer'])) {
                        $l_manufacturer = isys_import_handler::check_dialog('isys_pc_manufacturer', $l_data['manufacturer']);
                    } else {
                        $l_manufacturer = null;
                    }

                    $l_import_data = [
                        'data_id'    => null,
                        'properties' => [
                            'title'              => [
                                'value' => $l_data['title']
                            ],
                            'manufacturer'       => [
                                'value' => $l_manufacturer
                            ],
                            'active'             => [
                                'value' => null
                            ],
                            'model'              => [
                                'value' => null
                            ],
                            'volt'               => [
                                'value' => null
                            ],
                            'watt'               => [
                                'value' => null
                            ],
                            'ampere'             => [
                                'value' => null
                            ],
                            'btu'                => [
                                'value' => null
                            ],
                            'assigned_connector' => [
                                'value' => null
                            ],
                            'connector_sibling'  => [
                                'value' => null
                            ],
                            'description'        => [
                                'value' => null
                            ]
                        ]
                    ];

                    $l_pc_id = $powerConsumerDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                    // Emit category signal (afterCategoryEntrySave).
                    $this->container->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $powerConsumerDao,
                        $l_pc_id,
                        !!$l_pc_id,
                        $objId,
                        $l_import_data,
                        isset($l_changes) ? $l_changes : []
                    );

                    // Add it to existing ifaces
                    if (is_numeric($l_pc_id)) {
                        $l_existing_ps[] = [
                            'data_id' => $l_pc_id,
                            'title'   => $l_ps_data['TITLE']
                        ];
                    }
                }
            }

            // Interface sync
            if ($l_hw_key === 'interfaces') {
                foreach ($l_row as $l_interface_data) {
                    if ($l_interface_data === null) {
                        continue;
                    }

                    $l_data = [
                        'title'        => $l_interface_data['REFERENCE'],
                        'firmware'     => $l_interface_data['FIRMWARE'],
                        'serial'       => $l_interface_data['SERIALNUMBER'],
                        'manufacturer' => $l_interface_data['MANUFACTURER'],
                        'description'  => $l_interface_data['DESCRIPTION']
                    ];

                    if ($logbookActive) {
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                            'title' => ['value' => $l_interface_data['REFERENCE']]
                        ];
                        $l_changes = $this->getLogbook()
                            ->prepare_changes($interfaceDao, null, $l_category_values);
                        if (is_countable($l_changes) && count($l_changes) > 0) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                    serialize($l_changes)
                                );
                        }
                    }

                    $l_import_data = $interfaceDao->parse_import_array($l_data);

                    $l_interface_id = $interfaceDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                    // Emit category signal (afterCategoryEntrySave).
                    $this->container->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $interfaceDao,
                        $l_interface_id,
                        !!$l_interface_id,
                        $objId,
                        $l_import_data,
                        isset($l_changes) ? $l_changes : []
                    );
                    // Add it to existing ifaces
                    if (is_numeric($l_interface_id)) {
                        $l_existing_ifaces[] = [
                            'data_id' => $l_interface_id,
                            'title'   => $l_interface_data['REFERENCE']
                        ];
                    }
                }
                continue;
            }

            if (strtoupper($l_row['STATUS']) == 'UP') {
                $l_status = '1';
            } else {
                $l_status = '0';
            }

            // Port sync
            if ($l_sync_port && isset($l_row['TYPE']) && !isset($l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']])) {
                $l_data = [
                    'title'       => $l_row['SLOT'],
                    // 'Port ' . $l_hw_key,
                    'port_type'   => $l_row['TYPE'],
                    'speed'       => $l_speed,
                    'speed_type'  => $l_speed_type_id,
                    'mac'         => $l_row['MACADDR'],
                    'active'      => $l_status,
                    'description' => $l_row['DESCRIPTION']
                ];

                // Interface to port
                if (isset($l_interface_id)) {
                    $l_data['interface'] = $l_interface_id;
                }

                if (!$l_sync_ip) {
                    // add hostaddress
                    $l_data['addresses'] = [
                        $l_ip_id
                    ];
                }

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'       => ['value' => $l_row['SLOT']],
                        'port_type'   => ['title_lang' => $l_row['TYPE']],
                        'speed'       => ['value' => $l_row['SPEED']],
                        'speed_type'  => ['title_lang' => $l_speed_type],
                        'mac'         => ['value' => $l_row['MACADDR']],
                        'active'      => [
                            'value' => ($l_status) ? isys_application::instance()->container->get('language')
                                ->get('LC__UNIVERSAL__YES') : isys_application::instance()->container->get('language')
                                ->get('LC__UNIVERSAL__NO')
                        ],
                        'interface'   => ['value' => $l_row['SLOT']],
                        'description' => ['value' => $l_row['DESCRIPTION']]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($portDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent(
                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                "-modified from OCS-",
                                $objId,
                                $objType,
                                'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT',
                                serialize($l_changes)
                            );
                    }
                }

                $l_import_data = $portDao->parse_import_array($l_data);
                $l_port_id = $portDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);
                $l_arr = new SplFixedArray(2);
                $l_arr[0] = $l_port_id;
                $l_arr[1] = $l_row['DESCRIPTION'];
                $l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']] = $l_arr;

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $portDao,
                    $l_port_id,
                    !!$l_port_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }

            // Cache Port connections this will be handled after the categories has been imported for this device
            if (isset($l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']])) {
                if (!isset($portConnections[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]]) && !empty($l_row['DEVICEADDRESS']) &&
                    !empty($l_row['DEVICENAME'])) {
                    $l_fixed_array = new SplFixedArray(3);
                    $l_fixed_array[0] = $l_row['DEVICENAME'];
                    $l_fixed_array[1] = $l_row['DEVICEPORT'];
                    $l_fixed_array[2] = (isset($l_row['connected_to'])) ? $l_row['connected_to'] : $l_row['DEVICEMACADDR'];
                    $portConnections[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]] = $l_fixed_array;
                }

                if (!isset($portDescriptions[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]]) &&
                    $l_row['DESCRIPTION'] != $l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][1] && $l_row['DESCRIPTION'] != '') {
                    $portDescriptions[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]] = $l_row['DESCRIPTION'];
                }
            }

            // Ip sync
            if ($l_sync_ip && isset($l_row['IPADDR'])) {

                // ip type check ipv4, ipv6
                if ($l_row['IPADDR'] != '' && $l_row['IPMASK'] != '') {
                    // Calculate net ip
                    $l_subnet = Ip::validate_net_ip($l_row['IPADDR'], $l_row['IPMASK'], null, true);
                    $l_dhcp = false;
                    $l_address = $l_row['IPADDR'];
                } elseif (Ip::ip2long($l_row['IPDHCP']) > 0 && $l_row['IPSUBNET'] != '0.0.0.0') {
                    /*if(($l_pos = strpos($l_row['IPDHCP'],'.0')) > 0){
                        $l_subnet = substr($l_row['IPDHCP'], 0, ($l_pos));
                        $l_amount = substr_count($l_subnet, '.');
                        while($l_amount < 3){
                            $l_amount++;
                            $l_subnet .= '.0';
                        }
                    } else{
                        $l_ip_arr = explode('.', $l_row['IPDHCP']);
                        $l_subnet = $l_ip_arr[0].'.'.$l_ip_arr[1].'.'.$l_ip_arr[2].'.0';
                    }*/
                    $l_subnet = $l_row['IPSUBNET'];
                    $l_dhcp = true;
                    $l_address = $l_row['IPDHCP'];
                }

                $l_primary = (int)$l_row['PRIMARY'];

                // Secondary Check
                if ($l_address !== null && !in_array($l_address, $l_already_imported_ips)) {
                    if (defined('C__CATS_NET_TYPE__IPV4') && Ip::validate_ip($l_subnet)) {
                        $l_net_type = C__CATS_NET_TYPE__IPV4;
                        $l_cidr_suffix = Ip::calc_cidr_suffix($l_row['IPMASK']);
                        if ($l_dhcp) {
                            $l_ip_type = defined_or_default('C__CATP__IP__ASSIGN__DHCP', 1);
                        } else {
                            $l_ip_type = defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2);
                        }
                        $l_net_id = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');
                    } elseif (defined('C__CATS_NET_TYPE__IPV6') && Ip::validate_ipv6($l_subnet)) {
                        $l_net_type = C__CATS_NET_TYPE__IPV6;
                        $l_cidr_suffix = Ip::calc_cidr_suffix_ipv6($l_row['IPMASK']);
                        if ($l_dhcp) {
                            $l_ip_type = defined_or_default('C__CMDB__CATG__IP__DHCPV6', 1);
                        } else {
                            $l_ip_type = defined_or_default('C__CMDB__CATG__IP__STATIC', 2);
                        }
                        $l_net_id = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');
                    }

                    // Check Net
                    $availableNetsKey = $l_subnet . '|' . $l_cidr_suffix;
                    if (isset($availableNets[$availableNetsKey])) {
                        $l_net_id = $availableNets[$availableNetsKey]['row_data']['isys_cats_net_list__isys_obj__id'];

                        if ($availableNets[$availableNetsKey]['row_data']['isys_cats_net_list__address_range_from'] === '') {
                            // Update net because the range is not set
                            $l_ip_range = Ip::calc_ip_range($l_subnet, $availableNets[$availableNetsKey]['row_data']['isys_cats_net_list__mask']);
                            $l_from_long = Ip::ip2long($l_ip_range['from']);
                            $l_to_long = Ip::ip2long($l_ip_range['to']);
                            $availableNets[$availableNetsKey]['row_data']['isys_cats_net_list__address_range_from'] = $l_ip_range['from'];
                            $availableNets[$availableNetsKey]['row_data']['isys_cats_net_list__address_range_to'] = $l_ip_range['to'];
                            $l_update = 'UPDATE isys_cats_net_list SET isys_cats_net_list__address_range_from = ' . $ipDao->convert_sql_text($l_ip_range['from']) . ',
												isys_cats_net_list__address_range_to = ' . $ipDao->convert_sql_text($l_ip_range['to']) . ',
												isys_cats_net_list__address_range_from_long = ' . $ipDao->convert_sql_text($l_from_long) . ',
												isys_cats_net_list__address_range_to_long = ' . $ipDao->convert_sql_text($l_to_long) . '
												WHERE isys_cats_net_list__isys_obj__id = ' . $ipDao->convert_sql_id($l_net_id);
                            $ipDao->update($l_update);
                        }
                    } else {
                        // Create net
                        $l_gateway_arr = $ipDao->retrieve('SELECT isys_catg_ip_list__id FROM isys_catg_ip_list ' .
                            'INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id ' .
                            'WHERE isys_cats_net_ip_addresses_list__title = ' . $ipDao->convert_sql_text($l_row['IPGATEWAY']))
                            ->__to_array();
                        if ($l_gateway_arr) {
                            $l_gateway_id = $l_gateway_arr['isys_catg_ip_list__id'];
                        } else {
                            $l_gateway_id = null;
                        }
                        $l_net_id = $ipDao->insert_new_obj(defined_or_default('C__OBJTYPE__LAYER3_NET'), false, $l_subnet . '/' . $l_cidr_suffix, null, C__RECORD_STATUS__NORMAL);
                        $l_ip_range = Ip::calc_ip_range($l_subnet, $l_row['IPMASK']);
                        isys_cmdb_dao_category_s_net::instance($this->container->database)
                            ->create(
                                $l_net_id,
                                C__RECORD_STATUS__NORMAL,
                                $l_subnet,
                                $l_net_type,
                                $l_subnet,
                                $l_row['IPMASK'],
                                $l_gateway_id,
                                false,
                                $l_ip_range['from'],
                                $l_ip_range['to'],
                                null,
                                null,
                                '',
                                $l_cidr_suffix
                            );

                        $availableNets[$availableNetsKey] = [
                            'row_data' => [
                                'isys_cats_net_list__isys_obj__id' => $l_net_id,
                                'isys_obj__title'                  => $l_subnet,
                            ]
                        ];
                    }

                    $l_data = [
                        'net_type' => $l_net_type,
                        'primary'  => $l_primary,
                        'active'   => '1',
                        'net'      => $l_net_id,
                        'hostname' => ($l_primary) ? (strpos($deviceTitle, '.') !== false ? substr($deviceTitle, 0, strpos($deviceTitle, '.')) : $deviceTitle) : ''
                    ];

                    if (strpos($deviceTitle, '.') !== false && $l_primary) {
                        $l_data['dns_domain'] = isys_cmdb_dao_dialog::instance($this->container->database)
                            ->check_dialog('isys_net_dns_domain', substr($deviceTitle, strpos($deviceTitle, '.') + 1));
                    } else {
                        $l_data['dns_domain'] = null;
                    }

                    if ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                        $l_data['ipv4_address'] = $l_address;
                        $l_data['ipv4_assignment'] = $l_ip_type;
                    }
                    if ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                        $l_data['ipv6_address'] = $l_address;
                        $l_data['ipv6_assignment'] = $l_ip_type;
                    }

                    // add port
                    if ($l_port_id > 0) {
                        $l_data['assigned_port'] = $l_port_id;
                    } else {
                        $l_data['assigned_port'] = null;
                    }

                    if ($logbookActive) {
                        if ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                            $l_ip_assignment = $ipDao->get_dialog('isys_ip_assignment', $l_ip_type)
                                ->get_row();
                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                'ipv4_address'    => ['value' => $l_row["IPADDR"]],
                                'ipv4_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                'net_type'        => ['title_lang' => 'IPv4']
                            ];
                        } elseif ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                            $l_ip_assignment = $ipDao->get_dialog('isys_ipv6_assignment', $l_ip_type)
                                ->get_row();
                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                'ipv6_address'    => ['value' => $l_row["IPADDR"]],
                                'ipv6_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                'net_type'        => ['title_lang' => 'IPv6'],
                            ];
                        }

                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['hostname'] = [
                            'value' => (($l_primary) ? substr($deviceTitle, 0, strpos($deviceTitle, '.')) : null)
                        ];
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['net'] = ['value' => $l_net_id];
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['active'] = ['value' => 1];
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['primary'] = ['value' => $l_primary];

                        $l_changes = $this->getLogbook()
                            ->prepare_changes($ipDao, null, $l_category_values);

                        if (is_countable($l_changes) && count($l_changes) > 0) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__CATG__IP_ADDRESS',
                                    serialize($l_changes)
                                );
                            unset($l_changes);
                        }
                    }

                    $l_import_data = $ipDao->parse_import_array($l_data);
                    $l_ip_data_id = $ipDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);
                    $l_already_imported_ips[$l_ip_data_id] = $l_address;

                    // Emit category signal (afterCategoryEntrySave).
                    $this->container->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $ipDao,
                        $l_ip_data_id,
                        !!$l_ip_data_id,
                        $objId,
                        $l_import_data,
                        isset($l_changes) ? $l_changes : []
                    );
                }
            }
            $l_counter++;
        }
        if (is_countable($l_check_iface) && count($l_check_iface) > 0) {
            $this->delete_entries_from_category(
                $l_check_iface,
                $interfaceDao,
                'isys_catg_netp_list',
                $objId,
                $objType,
                'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                $logbookActive
            );
        }

        if ($overwriteIpPorts && is_countable($l_check_port) && count($l_check_port) > 0) {
            foreach ($l_check_port as $l_val) {
                $l_cableConID = $cableConDao->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                $cableConDao->delete_cable_connection($l_cableConID);
                $cableConDao->delete_connector($l_val["assigned_connector"]);
                $portDao->delete_entry($l_val['data_id'], 'isys_catg_port_list');

                if ($logbookActive) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_PURGED', "-modified from OCS-", $objId, $objType, 'LC__CATD__PORT', null);
                }
            }
        }

        if ($overwriteIpPorts && is_countable($l_check_ip) && count($l_check_ip) > 0) {
            $this->delete_entries_from_category($l_check_ip, $ipDao, 'isys_catg_ip_list', $objId, $objType, 'LC__CATG__IP_ADDRESS', $logbookActive);
        }

        // Import Connection between ports
        if (is_countable($portConnections) && count($portConnections) > 0) {
            $this->getLogger()
                ->info("Updating port connections.");
            foreach ($portConnections as $l_port_id => $l_connected_to) {
                $l_device_name = $l_connected_to[0];
                $l_port = $l_connected_to[1];
                $l_mac = $l_connected_to[2];
                //list($l_device_name, $l_port, $l_mac) = explode('|', $l_connected_to);

                //$l_connected_obj_id = $l_daoCMDB->get_object_by_hostname_serial_mac(null, null, $l_mac, $l_device_name);

                $l_connected_obj_id = $this->getMatcher()
                    ->get_object_id_by_matching(null, [$l_mac], $l_device_name, null, (Mac::getBit() + ObjectTitle::getBit()), 2);

                $l_cable_id = null;

                if ($l_connected_obj_id) {
                    $l_main_sql = 'SELECT isys_catg_port_list__id AS port_id, isys_catg_port_list__isys_catg_connector_list__id AS con_id, isys_catg_connector_list__isys_cable_connection__id AS cable_con
                                    FROM isys_catg_port_list
                                    INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
                                    WHERE ';
                    $l_sql = $l_main_sql . ' isys_catg_port_list__isys_obj__id = ' . $portDao->convert_sql_id($l_connected_obj_id);
                    if ($l_port) {
                        $l_sql .= ' AND isys_catg_port_list__title = ' . $portDao->convert_sql_text($l_port);
                    }

                    if ($l_mac) {
                        $l_sql .= ' AND isys_catg_port_list__mac = ' . $portDao->convert_sql_text($l_mac);
                    }
                    $l_connected_port = $portDao->retrieve($l_sql)
                        ->get_row();

                    $l_sql = $l_main_sql . ' isys_catg_port_list__id = ' . $portDao->convert_sql_id($l_port_id);
                    $l_main_port = $portDao->retrieve($l_sql)
                        ->get_row();

                    // Check if port is not assigned
                    if ($l_connected_port['cable_con'] != $l_main_port['cable_con'] || $l_connected_port['cable_con'] === null || $l_main_port['cable_con'] === null) {
                        if ($l_main_port['cable_con'] !== null) {
                            $l_cable_id = $cableConDao->get_cable_object_id_by_connection_id($l_main_port['cable_con']);
                            $cableConDao->delete_cable_connection($l_main_port['cable_con']);
                        }
                        if ($l_connected_port['cable_con'] !== null) {
                            if (!$l_cable_id) {
                                $l_cable_id = $cableConDao->get_cable_object_id_by_connection_id($l_main_port['cable_con']);
                            }
                            $cableConDao->delete_cable_connection($l_connected_port['cable_con']);
                        }

                        if (!$l_cable_id) {
                            $l_cable_id = isys_cmdb_dao_cable_connection::add_cable();
                        }

                        $portDao->connection_save($l_main_port['con_id'], $l_connected_port['con_id'], $l_cable_id);
                    }
                }
            }
        }

        if (is_countable($portDescriptions) && count($portDescriptions) > 0) {
            $this->getLogger()
                ->info("Updating port descriptions.");
            foreach ($portDescriptions as $l_port_id => $l_description) {
                $l_update = 'UPDATE isys_catg_port_list SET isys_catg_port_list__description = ' . $portDao->convert_sql_text($l_description) .
                    ' WHERE isys_catg_port_list__id = ' . $portDao->convert_sql_id($l_port_id);
                $portDao->update($l_update);
            }
            $portDao->apply_update();
        }
    }

    /**
     * @param      $objId
     * @param      $objType
     * @param      $uiData
     * @param      $connectionTypeTitle
     * @param bool $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleUi($objId, $objType, $uiData, $connectionTypeTitle, $logbookActive = false)
    {
        $l_daoUI = isys_cmdb_dao_category_g_ui::instance($this->container->database);
        $cableConDao = isys_cmdb_dao_cable_connection::instance($this->container->database);

        $l_check_data = [];

        $l_res_ui = $l_daoUI->retrieve('SELECT isys_catg_ui_list__id, isys_catg_ui_list__title, isys_catg_ui_list__isys_catg_connector_list__id, ' .
            'isys_ui_plugtype__title ' . 'FROM isys_catg_ui_list ' .
            'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id ' .
            'LEFT JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id ' . 'WHERE isys_catg_ui_list__isys_obj__id = ' .
            $l_daoUI->convert_sql_id($objId));
        $l_ui_amount = $l_res_ui->num_rows();

        if ($l_ui_amount > 0) {
            // data from i-doit
            while ($l_row_ui = $l_res_ui->get_row()) {
                $l_check_data[] = [
                    'data_id'            => $l_row_ui['isys_catg_ui_list__id'],
                    'title'              => $l_row_ui['isys_catg_ui_list__title'],
                    'plug'               => $l_row_ui['isys_ui_plugtype__title'],
                    'assigned_connector' => $l_row_ui['isys_catg_ui_list__isys_catg_connector_list__id'],
                    'description'        => $l_row_ui['isys_catg_ui_list__description']
                ];
            }

            if (is_countable($uiData) && count($uiData) > 0) {
                foreach ($uiData as $l_row) {
                    // Check if data already exists in i-doit
                    foreach ($l_check_data as $l_key => $l_value) {
                        if ($l_value['title'] == $l_row["NAME"] && $l_value['plug'] == $l_row['TYPE']) {
                            unset($l_check_data[$l_key]);
                            continue 2;
                        }
                    }

                    // Create new data
                    $l_data = [
                        'title'       => $l_row["NAME"],
                        'plug'        => $l_row["TYPE"],
                        'type'        => $connectionTypeTitle,
                        'description' => $l_row['DESCRIPTION'],
                    ];

                    if ($logbookActive) {
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                            'title' => ['value' => $l_row["NAME"]],
                            'plug'  => ['title_lang' => $l_row["TYPE"]],
                            'type'  => ['title_lang' => $connectionTypeTitle],
                        ];

                        $l_changes = $this->getLogbook()
                            ->prepare_changes($l_daoUI, null, $l_category_values);

                        if (is_countable($l_changes) && count($l_changes) > 0) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                    serialize($l_changes)
                                );
                        }
                    }

                    $l_import_data = $l_daoUI->parse_import_array($l_data);
                    $l_entry_id = $l_daoUI->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                    // Emit category signal (afterCategoryEntrySave).
                    $this->container->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $l_daoUI,
                        $l_entry_id,
                        !!$l_entry_id,
                        $objId,
                        $l_import_data,
                        isset($l_changes) ? $l_changes : []
                    );
                }
            }

            foreach ($l_check_data as $l_val) {
                $l_cableConID = $cableConDao->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                $cableConDao->delete_cable_connection($l_cableConID);
                $cableConDao->delete_connector($l_val["assigned_connector"]);
                $l_daoUI->delete_entry($l_val['data_id'], 'isys_catg_ui_list');

                if ($logbookActive) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_PURGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__UNIVERSAL_INTERFACE', null);
                }
            }
        } else {
            // create
            foreach ($uiData as $l_row) {
                $l_data = [
                    'title'       => $l_row["NAME"],
                    'plug'        => $l_row["TYPE"],
                    'type'        => $connectionTypeTitle,
                    'description' => $l_row['DESCRIPTION'],
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title' => ['value' => $l_row["NAME"]],
                        'plug'  => ['title_lang' => $l_row["TYPE"]],
                        'type'  => ['title_lang' => $connectionTypeTitle],
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($l_daoUI, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent(
                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                "-modified from OCS-",
                                $objId,
                                $objType,
                                'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                serialize($l_changes)
                            );
                    }
                }

                $l_import_data = $l_daoUI->parse_import_array($l_data);
                $l_entry_id = $l_daoUI->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $l_daoUI->get_category_id(),
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }
        }
    }

    /**
     * @param      $objId
     * @param      $objType
     * @param      $storageData
     * @param      $capacityUnit
     * @param bool $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleStorage($objId, $objType, $storageData, $capacityUnit, $logbookActive = false)
    {
        $l_daoStor = isys_cmdb_dao_category_g_stor::instance($this->container->database);
        $l_check_data = [];

        $l_res_stor = $l_daoStor->retrieve('SELECT isys_catg_stor_list__title, isys_stor_manufacturer__title, isys_stor_model__title, ' .
            'isys_catg_stor_list__capacity, isys_catg_stor_list__id ' . 'FROM isys_catg_stor_list ' .
            'LEFT JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id ' .
            'LEFT JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id ' . 'WHERE isys_catg_stor_list__isys_obj__id = ' .
            $l_daoStor->convert_sql_id($objId));

        $l_stor_amount = $l_res_stor->num_rows();

        if ($l_stor_amount > 0) {
            // Check, Delete, Create
            while ($l_row = $l_res_stor->get_row()) {
                $l_check_data[] = [
                    'data_id'      => $l_row['isys_catg_stor_list__id'],
                    'title'        => $l_row['isys_catg_stor_list__title'],
                    'manufacturer' => $l_row['isys_stor_manufacturer__title'],
                    'model'        => $l_row['isys_stor_model__title'],
                    'serial'       => $l_row['isys_catg_stor_list__serial'],
                    'capacity'     => $l_row['isys_catg_stor_list__capacity']
                ];
            }

            if (is_countable($storageData) && count($storageData) > 0) {
                foreach ($storageData as $l_row) {
                    // Check if data already exists in i-doit
                    foreach ($l_check_data as $l_key => $l_value) {
                        if ($l_value['title'] == $l_row["NAME"] && $l_value['serial'] == $l_row['SERIALNUMBER'] && $l_value['manufacturer'] == $l_row['MANUFACTURER'] &&
                            $l_value['model'] == $l_row['MODEL'] && $l_value['capacity'] == isys_convert::memory($l_row['DISKSIZE'], $capacityUnit)) {
                            unset($l_check_data[$l_key]);
                            continue 2;
                        }
                    }

                    if ($l_row["TYPE"] == null || $l_row["TYPE"] == "") {
                        $l_deviceType = $this->parseStorageType($l_row["DESCRIPTION"]);
                    } else {
                        $l_deviceType = $this->parseStorageType($l_row["TYPE"]);
                    }

                    // Create new data
                    $l_data = [
                        'title'        => $l_row['NAME'],
                        'manufacturer' => $l_row['MANUFACTURER'],
                        'model'        => $l_row['MODEL'],
                        'serial'       => $l_row['SERIALNUMBER'],
                        'capacity'     => $l_row['DISKSIZE'],
                        'unit'         => $capacityUnit,
                        'description'  => $l_row['DESCRIPTION'],
                        'type'         => $l_deviceType
                    ];

                    if ($logbookActive) {
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                            'title'        => ['value' => $l_row["NAME"]],
                            'capacity'     => ['value' => $l_row["DISKSIZE"]],
                            'unit'         => ['title_lang' => 'MB'],
                            'manufacturer' => ['title_lang' => $l_row["MANUFACTURER"]],
                            'model'        => ['title_lang' => $l_row["MODEL"]],
                            'serial'       => ['value' => $l_row['SERIALNUMBER']]
                        ];

                        $l_changes = $this->getLogbook()
                            ->prepare_changes($l_daoStor, null, $l_category_values);

                        if (is_countable($l_changes) && count($l_changes) > 0) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__UNIVERSAL__DEVICES',
                                    serialize($l_changes)
                                );
                        }
                    }

                    $l_import_data = $l_daoStor->parse_import_array($l_data);
                    $l_entry_id = $l_daoStor->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                    // Emit category signal (afterCategoryEntrySave).
                    $this->container->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $l_daoStor,
                        $l_entry_id,
                        !!$l_entry_id,
                        $objId,
                        $l_import_data,
                        isset($l_changes) ? $l_changes : []
                    );
                }
            }

            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $l_daoStor, 'isys_catg_stor_list', $objId, $objType, 'LC__UNIVERSAL__DEVICES', $logbookActive);
            }
        } else {
            // create
            foreach ($storageData as $l_row) {
                if ($l_row["TYPE"] == null || $l_row["TYPE"] == "") {
                    $l_deviceType = $this->parseStorageType($l_row["DESCRIPTION"]);
                } else {
                    $l_deviceType = $this->parseStorageType($l_row["TYPE"]);
                }

                // Create new data
                $l_data = [
                    'title'        => $l_row['NAME'],
                    'manufacturer' => $l_row['MANUFACTURER'],
                    'model'        => $l_row['MODEL'],
                    'capacity'     => $l_row['DISKSIZE'],
                    'unit'         => $capacityUnit,
                    'description'  => $l_row['DESCRIPTION'],
                    'type'         => $l_deviceType
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'        => ['value' => $l_row["NAME"]],
                        'capacity'     => ['value' => $l_row["DISKSIZE"]],
                        'unit'         => ['title_lang' => 'MB'],
                        'manufacturer' => ['title_lang' => $l_row["MANUFACTURER"]],
                        'model'        => ['title_lang' => $l_row["MODEL"]]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($l_daoStor, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__UNIVERSAL__DEVICES', serialize($l_changes));
                    }
                }

                $l_import_data = $l_daoStor->parse_import_array($l_data);
                $l_entry_id = $l_daoStor->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $l_daoStor,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }
        }
    }

    /**
     * @param      $objId
     * @param      $objType
     * @param      $applicationData
     * @param      $relationData
     * @param      $objectTitle
     * @param bool $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleApplications($objId, $objType, $applicationData, $relationData, $objectTitle, $logbookActive = false)
    {
        if (!defined('C__OBJTYPE__APPLICATION') || !defined('C__OBJTYPE__OPERATING_SYSTEM')) {
            return;
        }
        $l_regApp = isys_tenantsettings::get('ocs.application');
        $l_regAppAssign = isys_tenantsettings::get('ocs.application.assignment');
        $categoryDao = isys_cmdb_dao_category_g_application::instance($this->container->database);
        $specificCategoryDao = isys_cmdb_dao_category_s_application::instance($this->container->database);
        $relationDao = isys_cmdb_dao_category_g_relation::instance($this->container->database);

        $l_check_data = $l_double_assigned = [];
        $applicationQuery = "SELECT 
            isys_obj__title,
            isys_obj__id,
            isys_obj__isys_obj_type__id,
            isys_catg_version_list__title,
            isys_cats_app_variant_list__title,
            isys_catg_application_list__isys_cats_app_variant_list__id,
            isys_catg_application_list.*
            
            FROM isys_catg_application_list 
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id 
            INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id 
            LEFT JOIN isys_catg_version_list ON isys_catg_application_list__isys_catg_version_list__id = isys_catg_version_list__id
            LEFT JOIN isys_cats_app_variant_list ON isys_catg_application_list__isys_cats_app_variant_list__id = isys_cats_app_variant_list__id
            LEFT JOIN isys_cats_lic_list ON isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id 
            
            WHERE isys_catg_application_list__isys_obj__id = " . $categoryDao->convert_sql_id($objId) . " 
            AND (isys_obj_type__id = " . $categoryDao->convert_sql_id(C__OBJTYPE__APPLICATION) . " 
            OR isys_obj_type__id = " . $categoryDao->convert_sql_id(C__OBJTYPE__OPERATING_SYSTEM) . ");";

        $l_res_app = $categoryDao->retrieve($applicationQuery);

        while ($l_rowApp = $l_res_app->get_row()) {
            if (isset($l_check_data[$l_rowApp['isys_obj__id']])) {
                $l_double_assigned[] = [
                    'data_id' => $l_rowApp['isys_catg_application_list__id']
                ];
            }

            $assignedItServices = array_flip($categoryDao->get_assigned_it_services($l_rowApp["isys_catg_application_list__isys_catg_relation_list__id"]));
            $existingDatabases = $categoryDao->getAttachedDatabaseIds($l_rowApp['isys_catg_application_list__id']);

            $l_check_data[$l_rowApp['isys_obj__id']] = [
                'data_id'       => $l_rowApp['isys_catg_application_list__id'],
                'obj_type'      => (int)$l_rowApp['isys_obj__isys_obj_type__id'],
                'type'          => $l_rowApp['isys_catg_application_list__isys_catg_application_type__id'],
                'priority'      => $l_rowApp['isys_catg_application_list__isys_catg_application_priority__id'],
                'version'       => $l_rowApp['isys_catg_application_list__isys_catg_version_list__id'],
                'version_title' => $l_rowApp['isys_catg_version_list__title'],
                'assigned_variant'       => $l_rowApp['isys_catg_application_list__isys_cats_app_variant_list__id'],
                'assigned_license' => $l_rowApp['isys_catg_application_list__isys_cats_lic_list__id'],
                'assigned_it_services' => $assignedItServices,
                'assigned_databases' => $existingDatabases
            ];
        }
        $l_swIDs = $l_already_updated = [];
        // Assign Application
        foreach ($applicationData as $l_row) {
            $l_swID = false;

            $l_row['VERSION'] = trim($l_row['VERSION']);
            $l_row['COMMENTS'] = trim($l_row['COMMENTS']);
            $l_row['NAME'] = trim($l_row['NAME']);

            $l_app_objtype = ($l_row['COMMENTS'] === 'IOS') ? C__OBJTYPE__OPERATING_SYSTEM : C__OBJTYPE__APPLICATION;

            $l_resSW = $categoryDao->retrieve("SELECT isys_obj__id, isys_cats_application_list.* " . "FROM isys_obj " .
                "LEFT JOIN isys_cats_application_list ON isys_obj__id = isys_cats_application_list__isys_obj__id " . "WHERE isys_obj__title = " .
                $categoryDao->convert_sql_text($l_row["NAME"]) . " " . "AND isys_obj__isys_obj_type__id = " . $categoryDao->convert_sql_id($l_app_objtype) . ";");

            if ($l_resSW->num_rows() > 0) {
                // Application object exists
                $l_app_data = $l_resSW->get_row();
                $l_swID = $l_app_data['isys_obj__id'];
                if ($l_app_data['isys_cats_application_list__id'] > 0 && !in_array($l_app_data['isys_cats_application_list__id'], $l_already_updated)) {
                    $l_changed_data = [];
                    $l_specific_data = [
                        'data_id'          => $l_app_data['isys_cats_application_list__id'],
                        'specification'    => $l_app_data['isys_cats_application_list__specification'],
                        'installation'     => $l_app_data['isys_cats_application_list__isys_installation_type__id'],
                        'registration_key' => $l_app_data['isys_cats_application_list__registration_key'],
                        'manufacturer'     => $l_app_data['isys_cats_application_list__isys_application_manufacturer__id'],
                        'install_path'     => $l_app_data['isys_cats_application_list__install_path'],
                        'description'      => $l_app_data['isys_cats_application_list__description']
                    ];

                    if ($l_row['COMMENTS'] != '' && $l_row['COMMENTS'] != $l_app_data['isys_cats_application_list__description']) {
                        $l_changed_data['isys_cmdb_dao_category_s_application::description'] = [
                            'from' => $l_specific_data['description'],
                            'to'   => $l_row['COMMENTS']
                        ];
                        $l_specific_data['description'] = $l_row['COMMENTS'];
                    }

                    // Update specific category of application
                    if (is_countable($l_check_data) && count($l_changed_data) > 0) {
                        $specificCategoryDao->save(
                            $l_specific_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $l_specific_data['specification'],
                            $l_specific_data['manufacturer'],
                            null,
                            $l_specific_data['description'],
                            $l_specific_data['installation'],
                            $l_specific_data['registration_key'],
                            $l_specific_data['install_path']
                        );

                        if ($logbookActive) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $l_swID,
                                    C__OBJTYPE__APPLICATION,
                                    'LC__CMDB__CATS__APPLICATION',
                                    serialize($l_changed_data)
                                );
                        }
                    }
                    $l_already_updated[] = $l_app_data['isys_cats_application_list__id'];
                } elseif (!$l_app_data['isys_cats_application_list__id']) {
                    $l_app_data['isys_cats_application_list__id'] = $specificCategoryDao->create(
                        $l_swID,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        null,
                        null,
                        $l_row['COMMENTS'],
                        null,
                        null,
                        null
                    );
                    $l_already_updated[] = $l_app_data['isys_cats_application_list__id'];
                }
            } elseif ($l_regApp == "0") {
                // Creat new application object
                $l_swID = $categoryDao->insert_new_obj($l_app_objtype, false, $l_row["NAME"], null, C__RECORD_STATUS__NORMAL);
                $specificCategoryDao->create($l_swID, C__RECORD_STATUS__NORMAL, null, null, null, $l_row["COMMENTS"], null, null, null);
                isys_event_manager::getInstance()
                    ->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_swID, $l_app_objtype);
            }

            $l_version_id = null;

            // Check, if the found application version has already been created.
            if ($l_swID && !empty($l_row["VERSION"])) {
                // Check, if the version has been created.
                $l_app_version_sql = 'SELECT isys_catg_version_list__id FROM isys_catg_version_list
			                        WHERE isys_catg_version_list__isys_obj__id = ' . $categoryDao->convert_sql_id($l_swID) . '
			                        AND isys_catg_version_list__title LIKE ' . $categoryDao->convert_sql_text($l_row["VERSION"]) . ' LIMIT 1;';

                $l_res = $categoryDao->retrieve($l_app_version_sql);

                if (is_countable($l_res) && count($l_res)) {
                    $l_version_id = $l_res->get_row_value('isys_catg_version_list__id');
                } else {
                    $l_version_id = isys_cmdb_dao_category_g_version::instance($this->container->database)
                        ->create($l_swID, C__RECORD_STATUS__NORMAL, $l_row["VERSION"]);
                }
            }

            if ($l_swID && !in_array($l_swID, $l_swIDs)) {
                $l_swIDs[] = $l_swID;
                $importMode = isys_import_handler_cmdb::C__CREATE;

                $data = [
                    'isys_obj__id' => $objId,
                    'application' => $l_swID
                ];

                if (is_countable($l_check_data) && count($l_check_data) > 0 && isset($l_check_data[$l_swID])) {
                    if ($l_check_data[$l_swID]['obj_type'] === C__OBJTYPE__OPERATING_SYSTEM) {
                        if ($l_check_data[$l_swID]['priority'] !== defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY', 1)) {
                            // Update operating system
                            $l_update = 'UPDATE isys_catg_application_list SET isys_catg_application_list__isys_catg_application_priority__id = ' .
                                $categoryDao->convert_sql_id(defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY', 1)) . ' WHERE isys_catg_application_list__id = ' .
                                $categoryDao->convert_sql_id($l_check_data[$l_swID]['data_id']);
                            $categoryDao->update($l_update);

                            $l_model_data = $categoryDao->retrieve('SELECT isys_catg_model_list__firmware AS firmware, isys_catg_model_list__id AS id FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' .
                                $categoryDao->convert_sql_id($objId))
                                ->get_row();
                            if (empty($l_model_data['firmware'])) {
                                $l_update = 'UPDATE isys_catg_model_list SET isys_catg_model_list__firmware = ' . $categoryDao->convert_sql_text($l_row['NAME']) .
                                    ' WHERE isys_catg_model_list__id = ' . $categoryDao->convert_sql_id($l_model_data['id']);
                                $categoryDao->update($l_update);
                            }
                        }
                    }

                    if ((int)$l_check_data[$l_swID]['version'] !== (int)$l_version_id) {
                        // Update version
                        $l_update = 'UPDATE isys_catg_application_list SET isys_catg_application_list__isys_catg_version_list__id = ' .
                            $categoryDao->convert_sql_id($l_version_id) . ' WHERE isys_catg_application_list__id = ' .
                            $categoryDao->convert_sql_id($l_check_data[$l_swID]['data_id']);
                        $categoryDao->update($l_update);

                        if ($logbookActive) {
                            $l_changed_data['isys_cmdb_dao_category_g_application::application'] = [
                                'from' => $l_row["NAME"],
                                'to'   => $l_row["NAME"]
                            ];
                            $l_changed_data['isys_cmdb_dao_category_g_application::assigned_version'] = [
                                'from' => $l_check_data[$l_swID]['version_title'],
                                'to'   => $l_row['VERSION']
                            ];
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $objId,
                                    $objType,
                                    'LC__CMDB__CATG__APPLICATION',
                                    serialize($l_changed_data)
                                );
                        }
                    }

                    // Application found
                    unset($l_check_data[$l_swID]);
                    continue;
                } elseif ($l_app_objtype === C__OBJTYPE__OPERATING_SYSTEM) {
                    foreach ($l_check_data as $appId => $appData) {
                        if ($appData['obj_type'] !== C__OBJTYPE__OPERATING_SYSTEM) {
                            continue;
                        }

                        $data['data_id'] = $appData['data_id'];
                        $data['assigned_variant'] = $appData['assigned_variant'];
                        $data['assigned_license'] = $appData['assigned_license'];
                        $data['assigned_it_services'] = $appData['assigned_it_services'];
                        $data['assigned_databases'] = $appData['assigned_databases'];

                        $importMode = isys_import_handler_cmdb::C__UPDATE;
                        break;
                    }
                }

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = ['application' => ['value' => $l_swID]];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent(
                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                "-modified from OCS-",
                                $objId,
                                $objType,
                                'LC__CMDB__CATG__APPLICATION',
                                serialize($l_changes)
                            );
                    }
                }

                $appType = defined_or_default('C__CATG__APPLICATION_TYPE__SOFTWARE');
                $priority = null;

                if ($l_app_objtype == defined_or_default('C__OBJTYPE__OPERATING_SYSTEM')) {
                    $appType = defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM');
                    $priority = defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY');

                    $l_model_data = $categoryDao->retrieve('SELECT isys_catg_model_list__firmware AS firmware, isys_catg_model_list__id AS id 
                            FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' . $categoryDao->convert_sql_id($objId))
                        ->get_row();
                    if (empty($l_model_data['firmware'])) {
                        $l_update = 'UPDATE isys_catg_model_list SET isys_catg_model_list__firmware = ' . $categoryDao->convert_sql_text($l_row['NAME']) .
                            ' WHERE isys_catg_model_list__id = ' . $categoryDao->convert_sql_id($l_model_data['id']);
                        $categoryDao->update($l_update);
                    }
                }

                $data['assigned_version'] = $l_version_id;
                $data['application_type'] = $appType;
                $data['application_priority'] = $priority;

                $categoryDao->sync($categoryDao->parse_import_array($data), $objId, $importMode);
            }
        }

        // Detach Applications
        if ($l_regAppAssign == "1") {
            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category(
                    $l_check_data,
                    $categoryDao,
                    'isys_catg_application_list',
                    $objId,
                    $objType,
                    'LC__CMDB__CATG__APPLICATION',
                    $logbookActive
                );
            }
            if (is_countable($l_double_assigned) && count($l_double_assigned) > 0) {
                $this->delete_entries_from_category(
                    $l_double_assigned,
                    $categoryDao,
                    'isys_catg_application_list',
                    $objId,
                    $objType,
                    'LC__CMDB__CATG__APPLICATION',
                    $logbookActive
                );
            }
        }
    }
}
