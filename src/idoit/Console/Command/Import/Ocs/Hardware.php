<?php

namespace idoit\Console\Command\Import\Ocs;

use idoit\Component\Helper\Ip;
use isys_application;
use isys_cmdb_dao_cable_connection;
use isys_cmdb_dao_category_g_application;
use isys_cmdb_dao_category_g_cpu;
use isys_cmdb_dao_category_g_drive;
use isys_cmdb_dao_category_g_graphic;
use isys_cmdb_dao_category_g_ip;
use isys_cmdb_dao_category_g_memory;
use isys_cmdb_dao_category_g_model;
use isys_cmdb_dao_category_g_network_interface;
use isys_cmdb_dao_category_g_network_port;
use isys_cmdb_dao_category_g_operating_system;
use isys_cmdb_dao_category_g_relation;
use isys_cmdb_dao_category_g_sound;
use isys_cmdb_dao_category_g_stor;
use isys_cmdb_dao_category_g_ui;
use isys_cmdb_dao_category_g_version;
use isys_cmdb_dao_category_s_application;
use isys_cmdb_dao_category_s_net;
use isys_cmdb_dao_connection;
use isys_cmdb_dao_dialog;
use isys_convert;
use isys_event_manager;
use isys_import_handler;
use isys_import_handler_cmdb;
use isys_module_logbook;
use isys_tenantsettings;
use isys_component_dao_ocs;

class Hardware extends AbstractOcs
{
    /**
     * Builds array with all information that is neede for the import
     *
     * @param isys_component_dao_ocs $p_dao
     * @param array                  $p_hardwareids
     * @param array                  $p_categories
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function getDeviceInfo(
        $p_dao,
        $p_hardwareid = null,
        $p_add_model = false,
        $p_add_memory = false,
        $p_add_application = false,
        $p_add_graphic = false,
        $p_add_sound = false,
        $p_add_storage = false,
        $p_add_net = false,
        $p_add_ui = false,
        $p_add_drive = false,
        $p_add_vm = false,
        $addCpu = false
    ) {
        $l_memory = [];
        $l_app = [];
        $l_graphic = [];
        $l_sound = [];
        $l_drive = [];
        $l_net = [];
        $l_ui = [];
        $l_stor = [];

        $l_data = [
            'inventory' => $p_dao->getHardwareItem($p_hardwareid)
        ];

        $l_data['inventory']['macaddr'] = $p_dao->get_unique_mac_addresses($p_hardwareid);

        if ($p_add_model) {
            $l_data['model'] = $p_dao->getBios($p_hardwareid)
                ->__to_array();
        }

        if ($p_add_memory) {
            $l_res_ocs_memory = $p_dao->getMemory($p_hardwareid);
            while ($l_row = $l_res_ocs_memory->get_row()) {
                $l_memory[] = $l_row;
            }
            $l_data['memory'] = $l_memory;
        }

        if ($p_add_application) {
            $l_res_software = $p_dao->getSoftware($p_hardwareid);
            while ($l_row = $l_res_software->get_row()) {
                $l_app[] = $l_row;
            }
            $l_data['application'] = $l_app;
        }

        if ($p_add_graphic) {
            $l_res_ocs_graphic = $p_dao->getGraphicsAdapter($p_hardwareid);
            while ($l_row = $l_res_ocs_graphic->get_row()) {
                $l_graphic[] = $l_row;
            }
            $l_data['graphic'] = $l_graphic;
        }

        if ($p_add_sound) {
            $l_res_ocs_sound = $p_dao->getSoundAdapter($p_hardwareid);
            while ($l_row = $l_res_ocs_sound->get_row()) {
                $l_sound[] = $l_row;
            }
            $l_data['sound'] = $l_sound;
        }

        if ($p_add_drive) {
            $l_res_ocs_drive = $p_dao->getDrives($p_hardwareid);
            while ($l_row = $l_res_ocs_drive->get_row()) {
                $l_drive[] = $l_row;
            }
            $l_data['drive'] = $l_drive;
        }

        if ($p_add_net) {
            $l_res_net = $p_dao->getNetworkAdapter($p_hardwareid);
            while ($l_row = $l_res_net->get_row()) {
                $l_net[] = $l_row;
            }
            $l_data['net'] = $l_net;
        }

        if ($p_add_ui) {
            $l_res_ui = $p_dao->getPorts($p_hardwareid);
            while ($l_row = $l_res_ui->get_row()) {
                $l_ui[] = $l_row;
            }
            $l_data['ui'] = $l_ui;
        }

        if ($p_add_storage) {
            $l_res_stor = $p_dao->getStorage($p_hardwareid);
            while ($l_row = $l_res_stor->get_row()) {
                $l_stor[] = $l_row;
            }
            $l_data['stor'] = $l_stor;
        }

        if ($p_add_vm) {
            $l_res_vm = $p_dao->getVirtualMachines($p_hardwareid);
            while ($l_row = $l_res_vm->get_row()) {
                $l_vm[] = $l_row;
            }
            $l_data['virtual_machine'] = $l_vm;
        }

        if ($addCpu) {
            $cpuResult = $p_dao->getCPU($p_hardwareid);
            while ($data = $cpuResult->get_row()) {
                $data['SPEED'] = $data['SPEED'] / 1000;
                $cpuData[] = $data;
            }
            $l_data['cpu'] = $cpuData;
        }

        return $l_data;
    }

    /**
     * Handles category model for hardware devices
     *
     * @param                                $objId
     * @param                                $objType
     * @param                                $hardwareModelData
     * @param bool                           $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleModel($objId, $objType, $hardwareModelData, $logbookActive = false)
    {
        $categoryDao = isys_cmdb_dao_category_g_model::instance($this->container->database);
        $l_changes = $l_data = [];

        $l_rowModel = null;
        $l_row = $hardwareModelData;

        $l_res = $categoryDao->get_data(null, $objId);
        if ($l_res->num_rows() < 1) {
            $l_data['productid'] = null;
            $l_data['description'] = null;
        } else {
            $l_rowModel = $l_res->get_row();
            $l_data['productid'] = $l_rowModel["isys_catg_model_list__productid"];
            $l_data['description'] = $l_rowModel["isys_catg_model_list__description"];
            $l_data['data_id'] = $l_rowModel['isys_catg_model_list__id'];
        }

        $l_data['manufacturer'] = $l_row["SMANUFACTURER"];
        $l_data['title'] = $l_row['SMODEL'];
        $l_data['serial'] = $l_row["SSN"];
        $l_data['firmware'] = $l_row["BVERSION"];

        if ($logbookActive) {
            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                'manufacturer' => ['title_lang' => $l_row["SMANUFACTURER"]],
                'title'        => ['title_lang' => $l_row["SMODEL"]],
                'serial'       => ['value' => $l_row["SSN"]],
                'firmware'     => ['value' => $l_row["BVERSION"]]
            ];

            $l_changes = $this->getLogbook()
                ->prepare_changes($categoryDao, $l_rowModel, $l_category_values);
            if (is_countable($l_changes) && count($l_changes) > 0) {
                isys_event_manager::getInstance()
                    ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__MODEL', serialize($l_changes));
            }
        }

        $l_import_data = $categoryDao->parse_import_array($l_data);
        $l_entry_id = $categoryDao->sync($l_import_data, $objId, ((!empty($l_data['data_id'])) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE));

        // Emit category signal (afterCategoryEntrySave).
        $this->container->signals->emit("mod.cmdb.afterCategoryEntrySave", $categoryDao, $l_entry_id, !!$l_entry_id, $objId, $l_import_data, $l_changes);
    }

    /**
     * Handles category memory for hardware devices
     *
     * @param                                 $objId
     * @param                                 $objType
     * @param                                 $memoryData
     * @param                                 $memoryUnit
     * @param bool                            $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleMemory($objId, $objType, $memoryData, $memoryUnit, $logbookActive = false)
    {
        $l_changes = $l_check_data = [];
        $categoryDao = isys_cmdb_dao_category_g_memory::instance($this->container->database);
        $l_res = $categoryDao->get_data(null, $objId);
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

            foreach ($memoryData as $l_rowMemory) {

                // Values in ocs are in MB convert it to byte
                $convertedMemory = isys_convert::memory($l_rowMemory["CAPACITY"], $memoryUnit, C__CONVERT_DIRECTION__FORMWARD);
                $newMemoryUnit = isys_convert::get_memory_unit_const($convertedMemory, true);
                $memoryUnitTitle = str_replace('C__MEMORY_UNIT__', '', $newMemoryUnit);
                $newMemoryUnitId = isys_convert::get_memory_unit_const($convertedMemory);
                $l_rowMemory['CAPACITY'] = isys_convert::memory($convertedMemory, $newMemoryUnit, C__CONVERT_DIRECTION__BACKWARD);

                // Check data from ocs with data from i-doit
                foreach ($l_check_data as $l_key => $l_val) {
                    if ((float)$l_val['capacity'] === $convertedMemory &&
                        $l_val['title'] == $l_rowMemory['CAPTION'] &&
                        $l_val['type'] == $l_rowMemory['TYPE']) {
                        unset($l_check_data[$l_key]);
                        continue 2;
                    }
                }

                // Raw array for preparing the import array
                $l_data = [
                    'title'        => $l_rowMemory["CAPTION"],
                    'manufacturer' => null,
                    'type'         => $l_rowMemory['TYPE'],
                    'unit'         => $newMemoryUnitId,
                    'capacity'     => $l_rowMemory['CAPACITY']
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'    => ['title_lang' => $l_rowMemory["CAPTION"]],
                        'type'     => ['title_lang' => $l_rowMemory["TYPE"]],
                        'unit'     => ['title_lang' => $memoryUnitTitle],
                        'capacity' => ['value' => $l_rowMemory["CAPACITY"]]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__MEMORY', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit("mod.cmdb.afterCategoryEntrySave", $categoryDao, $l_entry_id, !!$l_entry_id, $objId, $l_import_data, $l_changes);
            }
            // Delete entries
            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $categoryDao, 'isys_catg_memory_list', $objId, $objType, 'LC__CMDB__CATG__MEMORY', $logbookActive);
            }
        } else {
            // Create entries
            foreach ($memoryData as $l_rowMemory) {

                // Values in ocs are in MB convert it to byte
                $convertedMemory = isys_convert::memory($l_rowMemory["CAPACITY"], $memoryUnit, C__CONVERT_DIRECTION__FORMWARD);
                $newMemoryUnit = isys_convert::get_memory_unit_const($convertedMemory, true);
                $memoryUnitTitle = str_replace('C__MEMORY_UNIT__', '', $newMemoryUnit);
                $newMemoryUnitId = isys_convert::get_memory_unit_const($convertedMemory);
                $l_rowMemory['CAPACITY'] = isys_convert::memory($convertedMemory, $newMemoryUnit, C__CONVERT_DIRECTION__BACKWARD);

                $l_data = [
                    'title'        => $l_rowMemory["CAPTION"],
                    'manufacturer' => null,
                    'type'         => $l_rowMemory["TYPE"],
                    'unit'         => $newMemoryUnitId,
                    'capacity'     => $l_rowMemory['CAPACITY']
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'    => ['title_lang' => $l_rowMemory["CAPTION"]],
                        'type'     => ['title_lang' => $l_rowMemory["TYPE"]],
                        'unit'     => ['title_lang' => $memoryUnitTitle],
                        'capacity' => ['value' => $l_rowMemory['CAPACITY']]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);
                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__MEMORY', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit("mod.cmdb.afterCategoryEntrySave", $categoryDao, $l_entry_id, !!$l_entry_id, $objId, $l_import_data, $l_changes);
            }
        }
    }

    /**
     * Handles category operating system for hardware devices
     *
     * @param                                           $objId
     * @param                                           $objType
     * @param                                           $osData
     * @param bool                                      $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleOperatingSystem($objId, $objType, $osData, $logbookActive = false)
    {
        if (!defined('C__OBJTYPE__OPERATING_SYSTEM')) {
            return;
        }
        $l_found = false;
        $l_data_id = null;
        $l_row_data = null;
        $categoryDao = isys_cmdb_dao_category_g_operating_system::instance($this->container->database);

        $l_os_sql = "SELECT isys_obj__id FROM isys_obj" . " WHERE isys_obj__title = " . $categoryDao->convert_sql_text($osData["OSNAME"]) .
            " AND isys_obj__isys_obj_type__id = " . $categoryDao->convert_sql_id(C__OBJTYPE__OPERATING_SYSTEM) . ";";

        $l_res = $categoryDao->retrieve($l_os_sql);

        if (is_countable($l_res) && count($l_res)) {
            // OS object exists.
            $l_row = $l_res->get_row();
            $l_osID = $l_row["isys_obj__id"];
        } else {
            // Create new OS object.
            $l_osID = $categoryDao->insert_new_obj(C__OBJTYPE__OPERATING_SYSTEM, false, $osData["OSNAME"], null, C__RECORD_STATUS__NORMAL);
        }

        $l_version_id = null;

        if ($l_osID > 0 && !empty($osData["OSVERSION"])) {
            // Check, if the version has been created.
            $l_os_version_sql = 'SELECT isys_catg_version_list__id FROM isys_catg_version_list
		                        WHERE isys_catg_version_list__isys_obj__id = ' . $categoryDao->convert_sql_id($l_osID) . '
		                        AND isys_catg_version_list__title LIKE ' . $categoryDao->convert_sql_text($osData["OSVERSION"]) . ' LIMIT 1;';

            $l_res = $categoryDao->retrieve($l_os_version_sql);

            if (is_countable($l_res) && count($l_res)) {
                $l_version_id = $l_res->get_row_value('isys_catg_version_list__id');
            } else {
                $l_version_id = isys_cmdb_dao_category_g_version::instance($this->container->database)
                    ->create($l_osID, C__RECORD_STATUS__NORMAL, $osData["OSVERSION"]);
            }
        }

        $l_data = [
            'data_id'          => $l_data_id,
            'application'      => $l_osID,
            'assigned_version' => $l_version_id,
        ];

        $l_res = $categoryDao->retrieve("SELECT * FROM isys_catg_application_list
             INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
             INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
             INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
             WHERE isys_catg_application_list__isys_obj__id = " . $categoryDao->convert_sql_id($objId) . " 
             AND isys_catg_application_list__isys_catg_application_priority__id = " . $categoryDao->convert_sql_id(C__CATG__APPLICATION_PRIORITY__PRIMARY) . "
             AND isys_catg_application_list__isys_catg_application_type__id = " . $categoryDao->convert_sql_id(C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM) . "
             AND isys_obj_type__id = " . $categoryDao->convert_sql_id(C__OBJTYPE__OPERATING_SYSTEM) . ";");

        if (is_countable($l_res) && count($l_res) > 1) {
            while ($l_rowOS = $l_res->get_row()) {
                if ($l_rowOS['isys_obj__title'] != $osData["OSNAME"]) {
                    $categoryDao->delete_entry($l_rowOS['isys_catg_application_list__id'], 'isys_catg_application_list');

                    if ($logbookActive) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_PURGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__APPLICATION', null);
                    }
                } else {
                    $l_found = true;
                }
            }
        } elseif (is_countable($l_res) && count($l_res) == 1) {
            $l_rowOS = $l_res->get_row();
            $l_row_data = [
                'isys_catg_application_list__id' => $l_rowOS['isys_catg_application_list__id'],
                'isys_connection__isys_obj__id'  => $l_rowOS['isys_connection__isys_obj__id'],
            ];

            $l_data_id = $l_rowOS['isys_catg_application_list__id'];
            $l_data['data_id'] = $l_data_id;
            $l_data['assigned_license'] = $l_rowOS['isys_catg_application_list__isys_cats_lic_list__id'];
            $l_data['assigned_variant'] = $l_rowOS['isys_catg_application_list__isys_cats_app_variant_list__id'];
            $l_data['description'] = $l_rowOS['isys_catg_application_list__description'];
            $l_data['assigned_it_service'] = $categoryDao->get_assigned_it_services($l_rowOS['isys_catg_application_list__isys_catg_relation_list__id']);
        }

        if (!$l_found) {
            if ($logbookActive) {
                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = ['application' => ['value' => $l_osID]];

                $l_changes = $this->getLogbook()
                    ->prepare_changes($categoryDao, $l_row_data, $l_category_values);

                if (is_countable($l_changes) && count($l_changes) > 0) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CATG__OPERATING_SYSTEM', serialize($l_changes));
                }
            }

            $l_import_data = $categoryDao->parse_import_array($l_data);
            $l_entry_id = $categoryDao->sync($l_import_data, $objId, (($l_data_id > 0) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE));

            // Emit category signal (afterCategoryEntrySave).
            $this->container->signals->emit(
                "mod.cmdb.afterCategoryEntrySave",
                $categoryDao,
                $l_entry_id,
                !!$l_entry_id,
                $objId,
                $l_import_data,
                isset($l_changes) ? $l_changes : []
            );
        }
    }

    /**
     * Handles category application for hardware devices
     *
     * @param                                      $objId
     * @param                                      $objType
     * @param                                      $applicationData
     * @param                                      $appManufacturers
     * @param                                      $relationData
     * @param                                      $objectTitle
     * @param bool                                 $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleApplications($objId, $objType, $applicationData, $appManufacturers, $relationData, $objectTitle, $logbookActive = false)
    {
        if (!defined('C__OBJTYPE__APPLICATION')) {
            return;
        }
        $l_check_data = $l_double_assigned = [];
        $categoryDao = isys_cmdb_dao_category_g_application::instance($this->container->database);
        $specificCategoryDao = isys_cmdb_dao_category_s_application::instance($this->container->database);
        $relationDao = isys_cmdb_dao_category_g_relation::instance($this->container->database);

        $l_res_app = $categoryDao->retrieve("SELECT isys_obj__title, isys_catg_application_list__id, isys_obj__id,
                            isys_catg_application_list__isys_catg_version_list__id, isys_catg_version_list__title " . "FROM isys_catg_application_list " .
            "INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id " .
            "INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " .
            "LEFT JOIN isys_catg_version_list ON isys_catg_application_list__isys_catg_version_list__id = isys_catg_version_list__id " .
            "WHERE isys_catg_application_list__isys_obj__id = " . $categoryDao->convert_sql_id($objId) . " " . "AND isys_obj_type__id = " .
            $categoryDao->convert_sql_id(C__OBJTYPE__APPLICATION));

        while ($l_rowApp = $l_res_app->get_row()) {
            if (isset($l_check_data[$l_rowApp['isys_obj__id']])) {
                $l_double_assigned[] = [
                    'data_id' => $l_rowApp['isys_catg_application_list__id']
                ];
            }

            $l_check_data[$l_rowApp['isys_obj__id']] = [
                'data_id'       => $l_rowApp['isys_catg_application_list__id'],
                'version'       => $l_rowApp['isys_catg_application_list__isys_catg_version_list__id'],
                'version_title' => $l_rowApp['isys_catg_version_list__title']
            ];
        }

        $l_swIDs = $l_already_updated = [];

        // Assign Application
        foreach ($applicationData as $l_row) {
            $l_swID = false;

            $l_row['VERSION'] = trim($l_row['VERSION']);
            $l_row['PUBLISHER'] = trim($l_row['PUBLISHER']);
            $l_row['FOLDER'] = trim($l_row['FOLDER']);
            $l_row['COMMENTS'] = trim($l_row['COMMENTS']);
            $l_row['NAME'] = trim($l_row['NAME']);

            $l_resSW = $categoryDao->retrieve("SELECT isys_obj__id, isys_cats_application_list.* " . "FROM isys_obj " .
                "LEFT JOIN isys_cats_application_list ON isys_obj__id = isys_cats_application_list__isys_obj__id " . "WHERE isys_obj__title = " .
                $categoryDao->convert_sql_text($l_row["NAME"]) . " " . "AND isys_obj__isys_obj_type__id = " . $categoryDao->convert_sql_id(C__OBJTYPE__APPLICATION) . ";");

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
                        'release'          => $l_app_data['isys_cats_application_list__release'],
                        'description'      => $l_app_data['isys_cats_application_list__description']
                    ];

                    if (strtolower($l_row['PUBLISHER']) != strtolower($appManufacturers[$l_specific_data['manufacturer']])) {
                        $l_changed_data['isys_cmdb_dao_category_s_application::manufacturer'] = [
                            'from' => $appManufacturers[$l_app_data['isys_cats_application_list__isys_application_manufacturer__id']],
                            'to'   => $l_row['PUBLISHER']
                        ];

                        $l_specific_data['manufacturer'] = isys_import_handler::check_dialog('isys_application_manufacturer', $l_row['PUBLISHER']);
                    }

                    if ($l_row['FOLDER'] != $l_app_data['isys_cats_application_list__install_path']) {
                        $l_changed_data['isys_cmdb_dao_category_s_application::install_path'] = [
                            'from' => $l_specific_data['install_path'],
                            'to'   => $l_row['FOLDER'],
                        ];

                        $l_specific_data['install_path'] = $l_row['FOLDER'];
                    }

                    if ($l_row['COMMENTS'] != '' && $l_row['COMMENTS'] != $l_app_data['isys_cats_application_list__description']) {
                        $l_changed_data['isys_cmdb_dao_category_s_application::description'] = [
                            'from' => $l_specific_data['description'],
                            'to'   => $l_row['COMMENTS']
                        ];

                        $l_specific_data['description'] = $l_row['COMMENTS'];
                    }

                    // Update specific category of application
                    if (is_countable($l_changed_data) && count($l_changed_data) > 0) {
                        $specificCategoryDao->save(
                            $l_specific_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $l_specific_data['specification'],
                            $l_specific_data['manufacturer'],
                            $l_specific_data['release'],
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
                    if ($l_row['PUBLISHER'] != '') {
                        $l_manufacturer = isys_import_handler::check_dialog('isys_application_manufacturer', $l_row['PUBLISHER']);
                    } else {
                        $l_manufacturer = array_search(isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__NOT_SPECIFIED'), $appManufacturers);
                    }

                    $l_app_data['isys_cats_application_list__id'] = $specificCategoryDao->create(
                        $l_swID,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        $l_manufacturer,
                        null,
                        $l_row['COMMENTS'],
                        null,
                        null,
                        $l_row['FOLDER']
                    );
                    $l_already_updated[] = $l_app_data['isys_cats_application_list__id'];
                }
            } elseif (isys_tenantsettings::get('ocs.application') == "0") {
                // Creat new application object
                $l_swID = $categoryDao->insert_new_obj(C__OBJTYPE__APPLICATION, false, $l_row["NAME"], null, C__RECORD_STATUS__NORMAL);
                if ($l_row['PUBLISHER'] != '' && ($l_app_man_key = array_search($l_row['PUBLISHER'], $appManufacturers))) {
                    $l_manufacturer = $l_app_man_key;
                } else {
                    if ($l_row['PUBLISHER'] != '') {
                        $l_manufacturer = isys_import_handler::check_dialog('isys_application_manufacturer', $l_row['PUBLISHER']);
                        $appManufacturers[$l_manufacturer] = $l_row['PUBLISHER'];
                    } else {
                        $l_manufacturer = array_search(isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__NOT_SPECIFIED'), $appManufacturers);
                    }
                }
                $specificCategoryDao->create($l_swID, C__RECORD_STATUS__NORMAL, null, $l_manufacturer, $l_row["VERSION"], $l_row["COMMENTS"], null, null, $l_row["FOLDER"]);
                isys_event_manager::getInstance()
                    ->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_swID, C__OBJTYPE__APPLICATION);
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
                if (is_countable($l_check_data) && count($l_check_data) > 0 && isset($l_check_data[$l_swID])) {
                    // Application found
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

                    unset($l_check_data[$l_swID]);
                    continue;
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

                // First create relation
                $l_relation_obj = $relationDao->create_object(
                    $relationDao->format_relation_name($objectTitle, $l_row['NAME'], $relationData["isys_relation_type__master"]),
                    defined_or_default('C__OBJTYPE__RELATION'),
                    C__RECORD_STATUS__NORMAL
                );

                $l_sql = "INSERT INTO isys_catg_relation_list " . "SET " . "isys_catg_relation_list__isys_obj__id = " . $categoryDao->convert_sql_id($l_relation_obj) . ", " .
                    "isys_catg_relation_list__isys_obj__id__master = " . $categoryDao->convert_sql_id($objId) . ", " . "isys_catg_relation_list__isys_obj__id__slave = " .
                    $categoryDao->convert_sql_id($l_swID) . ", " . "isys_catg_relation_list__isys_relation_type__id = '" . defined_or_default('C__RELATION_TYPE__SOFTWARE') . "', " .
                    "isys_catg_relation_list__isys_weighting__id = '" . defined_or_default('C__WEIGHTING__5', 5) . "', " . "isys_catg_relation_list__status = '" . C__RECORD_STATUS__NORMAL . "' " .
                    ";";

                if ($categoryDao->update($l_sql)) {
                    $l_relation_id = $categoryDao->get_last_insert_id();

                    // Secondly insert new application entry with relation id
                    $l_sql = "INSERT INTO isys_catg_application_list SET
										isys_catg_application_list__isys_connection__id = " .
                        $categoryDao->convert_sql_id(isys_cmdb_dao_connection::instance($this->container->database)
                            ->add_connection($l_swID)) . ",
										isys_catg_application_list__status = " . $categoryDao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
										isys_catg_application_list__isys_catg_relation_list__id = ' . $categoryDao->convert_sql_id($l_relation_id) . ',
										isys_catg_application_list__isys_obj__id = ' . $categoryDao->convert_sql_id($objId) . ',
										isys_catg_application_list__isys_catg_version_list__id = ' . $categoryDao->convert_sql_id($l_version_id) . ';';

                    $categoryDao->update($l_sql) && $categoryDao->apply_update();
                }
            }
        }

        // Detach Applications
        if ("1" == isys_tenantsettings::get('ocs.application.assignment')) {
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

    /**
     * @param                                  $objId
     * @param                                  $objType
     * @param                                  $graphicData
     * @param                                  $capacityUnitMB
     * @param bool                             $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleGraphic($objId, $objType, $graphicData, $capacityUnitMB, $logbookActive = false)
    {
        $l_check_data = [];
        $categoryDao = isys_cmdb_dao_category_g_graphic::instance($this->container->database);
        $l_res_graphic = $categoryDao->get_data(null, $objId);
        $l_graka_amount = $l_res_graphic->num_rows();

        if ($l_graka_amount > 0) {
            while ($l_rowGraka = $l_res_graphic->get_row()) {
                $l_check_data[] = [
                    'data_id'      => $l_rowGraka['isys_catg_graphic_list__id'],
                    'title'        => $l_rowGraka['isys_catg_graphic_list__title'],
                    'memory'       => $l_rowGraka['isys_catg_graphic_list__memory'],
                    'manufacturer' => $l_rowGraka['isys_graphic_manufacturer__title'],
                    'unit'         => $l_rowGraka['isys_catg_graphic_list__isys_graphic_manufacturer__id'],
                    'description'  => $l_rowGraka['isys_catg_graphic_list__description']
                ];
            }

            foreach ($graphicData as $l_rowGraka) {
                foreach ($l_check_data as $l_key => $l_val) {
                    if ($l_val['title'] == $l_rowGraka['NAME'] && $l_val['memory'] == isys_convert::memory($l_rowGraka["MEMORY"], $capacityUnitMB)) {
                        unset($l_check_data[$l_key]);
                        continue 2;
                    }
                }

                $l_data = [
                    'title'        => $l_rowGraka['NAME'],
                    'manufacturer' => null,
                    'memory'       => $l_rowGraka['MEMORY'],
                    'unit'         => $capacityUnitMB
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'  => ['value' => $l_rowGraka['NAME']],
                        'memory' => ['value' => $l_rowGraka['MEMORY']],
                        'unit'   => ['title_lang' => 'MB']
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent(
                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                "-modified from OCS-",
                                $objId,
                                $objType,
                                'LC__CMDB__CATG__GRAPHIC',
                                serialize($l_changes)
                            );
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }

            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $categoryDao, 'isys_catg_graphic_list', $objId, $objType, 'LC__CMDB__CATG__GRAPHIC', $logbookActive);
            }
        } else {
            foreach ($graphicData as $l_rowGraka) {
                $l_data = [
                    'title'        => $l_rowGraka['NAME'],
                    'manufacturer' => null,
                    'memory'       => $l_rowGraka['MEMORY'],
                    'unit'         => $capacityUnitMB,
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'    => ['value' => $l_rowGraka['NAME']],
                        'unit'     => ['title_lang' => 'MB'],
                        'capacity' => ['value' => $l_rowGraka['MEMORY']]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);
                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__MEMORY', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
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
     * @param                                $objId
     * @param                                $objType
     * @param                                $graphicData
     * @param bool                           $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleSound($objId, $objType, $graphicData, $logbookActive = false)
    {
        $l_check_data = [];
        $categoryDao = isys_cmdb_dao_category_g_sound::instance($this->container->database);
        $l_res_sound = $categoryDao->get_data(null, $objId);
        $l_sound_amount = $l_res_sound->num_rows();

        if ($l_sound_amount > 0) {
            while ($l_rowSound = $l_res_sound->get_row()) {
                $l_check_data[] = [
                    'data_id'      => $l_rowSound['isys_catg_sound_list__id'],
                    'title'        => $l_rowSound['isys_catg_sound_list__title'],
                    'manufacturer' => $l_rowSound['isys_sound_manufacturer__title'],
                    'description'  => $l_rowSound['isys_catg_graphic_list__description']
                ];
            }

            foreach ($graphicData as $l_rowSound) {
                foreach ($l_check_data as $l_key => $l_val) {
                    if ($l_val['title'] == $l_rowSound['NAME'] && $l_val['manufacturer'] == $l_rowSound['MANUFACTURER']) {
                        unset($l_check_data[$l_key]);
                        continue 2;
                    }
                }

                $l_data = [
                    'title'        => $l_rowSound['NAME'],
                    'manufacturer' => $l_rowSound['MANUFACTURER'],
                    'description'  => $l_rowSound['DESCRIPTION']
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'        => ['value' => $l_rowSound['NAME']],
                        'manufacturer' => ['title_lang' => $l_rowSound['MANUFA']],
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__SOUND', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }
            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $categoryDao, 'isys_catg_sound_list', $objId, $objType, 'LC__CMDB__CATG__SOUND', $logbookActive);
            }
        } else {
            foreach ($graphicData as $l_rowSound) {
                $l_data = [
                    'title'        => $l_rowSound['NAME'],
                    'manufacturer' => $l_rowSound['MANUFACTURER'],
                    'description'  => $l_rowSound['DESCRIPTION']
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'        => ['value' => $l_rowSound['NAME']],
                        'manufacturer' => ['title_lang' => $l_rowSound['MANUFACTURER']]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);
                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__SOUND', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
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
     * @param                                $objId
     * @param                                $objType
     * @param                                $driveData
     * @param                                $capacityUnit
     * @param bool                           $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleDrive($objId, $objType, $driveData, $capacityUnit, $logbookActive = false)
    {
        $l_check_data = [];
        $categoryDao = isys_cmdb_dao_category_g_drive::instance($this->container->database);
        $l_res_drive = $categoryDao->get_data(null, $objId);
        $l_drive_amount = $l_res_drive->num_rows();

        if ($l_drive_amount > 0) {
            // Create and delete
            while ($l_rowDrive = $l_res_drive->get_row()) {
                $l_check_data[] = [
                    'data_id'      => $l_rowDrive['isys_catg_drive_list__id'],
                    'mount_point'  => $l_rowDrive['isys_catg_drive_list__driveletter'],
                    'title'        => $l_rowDrive['isys_catg_drive_list__title'],
                    'system_drive' => $l_rowDrive['isys_catg_drive_list__system_drive'],
                    'filesystem'   => $l_rowDrive['isys_filesystem_type__title'],
                    'unit'         => $l_rowDrive['isys_catg_drive_list__isys_memory_unit__id'],
                    'capacity'     => $l_rowDrive['isys_catg_drive_list__capacity'],
                    'description'  => $l_rowDrive['isys_catg_drive_list__description'],
                ];
            }

            foreach ($driveData as $l_rowDrive) {
                if ($l_rowDrive["LETTER"] == null) {
                    $l_driveletter = $l_rowDrive["TYPE"];
                } else {
                    $l_driveletter = $l_rowDrive["LETTER"];
                }

                foreach ($l_check_data as $l_key => $l_val) {
                    if ($l_val['mount_point'] == $l_driveletter && $l_val['filesystem'] == $l_rowDrive['FILESYSTEM']) {
                        unset($l_check_data[$l_key]);
                        continue 2;
                    }
                }

                $l_data = [
                    'mount_point' => $l_driveletter,
                    'filesystem'  => $l_rowDrive['FILESYSTEM'],
                    'unit'        => $capacityUnit,
                    'capacity'    => $l_rowDrive['TOTAL'],
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'mount_point' => ['value' => $l_driveletter],
                        'filesystem'  => ['title_lang' => $l_rowDrive['FILESYSTEM']],
                        'unit'        => ['title_lang' => 'MB'],
                        'capacity'    => ['value' => $l_rowDrive['TOTAL']]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__STORAGE_DRIVE', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }

            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $categoryDao, 'isys_catg_drive_list', $objId, $objType, 'LC__STORAGE_DRIVE', $logbookActive);
            }
        } else {
            // Create
            foreach ($driveData as $l_rowDrive) {
                if ($l_rowDrive["LETTER"] == null) {
                    $l_driveletter = $l_rowDrive["TYPE"];
                } else {
                    $l_driveletter = $l_rowDrive["LETTER"];
                }

                $l_data = [
                    'mount_point' => $l_driveletter,
                    'filesystem'  => $l_rowDrive['FILESYSTEM'],
                    'unit'        => $capacityUnit,
                    'capacity'    => $l_rowDrive['TOTAL'],
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'mount_point' => ['value' => $l_driveletter],
                        'filesystem'  => ['title_lang' => $l_rowDrive['FILESYSTEM']],
                        'unit'        => ['title_lang' => 'MB'],
                        'capacity'    => ['value' => $l_rowDrive['TOTAL']]
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);
                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__STORAGE_DRIVE', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
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
     * @param                                            $objId
     * @param                                            $objType
     * @param array                                      $netData
     * @param                                            $deviceTitle
     * @param                                            $availableNets
     * @param bool                                       $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleNet($objId, $objType, array $netData, $deviceTitle, &$availableNets, $logbookActive = false, $overwriteIpPorts = false)
    {
        $l_check_ip = $l_check_net = $l_check_iface = $l_check_port = $l_already_imported_ips = [];
        $ipDao = isys_cmdb_dao_category_g_ip::instance($this->container->database);
        $portDao = isys_cmdb_dao_category_g_network_port::instance($this->container->database);
        $interfaceDao = isys_cmdb_dao_category_g_network_interface::instance($this->container->database);

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
                'hostname'  => $l_row_ip['isys_catg_ip_list__hostname']
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
                'assigned_connector' => $l_row_port['isys_catg_port_list__isys_catg_connector_list__id']
            ];
        }

        // Interface info
        $l_query_interface = 'SELECT isys_catg_netp_list__id, isys_catg_netp_list__title FROM isys_catg_netp_list ' . 'WHERE isys_catg_netp_list__isys_obj__id = ' .
            $interfaceDao->convert_sql_id($objId);
        $l_res_iface = $interfaceDao->retrieve($l_query_interface);
        while ($l_row_iface = $l_res_iface->get_row()) {
            $l_check_iface[] = [
                'data_id' => $l_row_iface['isys_catg_netp_list__id'],
                'title'   => $l_row_iface['isys_catg_netp_list__title']
            ];
        }

        foreach ($netData as $l_hw_key => $l_row) {
            preg_match('/[0-9]*/', $l_row['SPEED'], $l_speed_arr);
            $l_speed = $l_speed_arr[0];
            preg_match('/[^0-9][^\s]*/', $l_row['SPEED'], $l_speed_type_arr);
            $l_speed_type = ltrim($l_speed_type_arr[0]);
            $l_speed_type_lower = strtolower($l_speed_type);
            $l_speed_type_id = defined_or_default('C__PORT_SPEED__KBIT_S', 2);

            if ($l_speed_type_lower == 'mb/s' || $l_speed_type_lower == 'm' || $l_speed_type_lower == 'mb' || $l_speed_type_lower == 'mbps') {
                $l_speed_type_id = defined_or_default('C__PORT_SPEED__MBIT_S', 3);
            } elseif ($l_speed_type_lower == 'gb/s' || $l_speed_type_lower == 'g' || $l_speed_type_lower == 'gb' || $l_speed_type_lower == 'gbps') {
                $l_speed_type_id = defined_or_default('C__PORT_SPEED__GBIT_S', 4);
            }

            $l_address = null;
            $l_interface_id = null;
            $l_port_id = null;
            $l_ip_id = null;
            $l_sync_ip = true;
            $l_sync_port = true;
            $l_sync_iface = true;
            $l_cidr_suffix = null;

            if (is_countable($l_check_iface) && count($l_check_iface) > 0 && 1 == 2) {
                foreach ($l_check_iface as $l_key => $l_iface) {
                    if (strcasecmp($l_iface['title'], $l_row['DESCRIPTION']) == 0) {
                        $l_interface_id = $l_check_iface[$l_key]['data_id'];
                        unset($l_check_iface[$l_key]);
                        $l_sync_iface = false;
                        break;
                    }
                }
            }

            if (is_countable($l_check_port) && count($l_check_port) > 0) {
                foreach ($l_check_port as $l_key => $l_port) {
                    if ($l_port['port_type'] == $l_row['TYPE'] && strcmp($l_port['mac'], $l_row['MACADDR']) == 0 && $l_port['speed_type'] == $l_speed_type_id &&
                        $l_port['speed'] == isys_convert::speed($l_speed, $l_speed_type_id)) {
                        $l_port_id = $l_check_port[$l_key]['data_id'];
                        unset($l_check_port[$l_key]);
                        $l_sync_port = false;
                        break;
                    }
                }
            }

            if (is_countable($l_check_ip) && count($l_check_ip) > 0) {
                foreach ($l_check_ip as $l_key => $l_ip) {
                    if (($l_ip['title'] == $l_row['IPADDRESS'] || $l_ip['title'] == $l_row['IPDHCP']) && $l_ip['net_title'] == $l_row['IPSUBNET']) {
                        if (strtoupper($l_row['STATUS']) == 'UP') {
                            $compareHostname = (strpos($deviceTitle, '.') ? substr($deviceTitle, 0, strpos($deviceTitle, '.')) : $deviceTitle);
                            if ($l_ip['hostname'] != $compareHostname) {
                                continue;
                            }
                        } else {
                            if ($l_ip['hostname'] == '') {
                                continue;
                            }
                        }

                        $l_ip_id = $l_check_ip[$l_key]['data_id'];
                        unset($l_check_ip[$l_key]);
                        unset($l_check_net[$l_key]);
                        $l_already_imported_ips[$l_ip_id] = $l_ip['title'];
                        $l_sync_ip = false;
                        break;
                    }
                }
            }

            // Interface sync
            if ($l_sync_iface && 1 == 2) {
                $l_data = [
                    'title' => $l_row['DESCRIPTION']
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title' => ['value' => $l_row['DESCRIPTION']]
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
            }

            if (strtoupper($l_row['STATUS']) == 'UP') {
                $l_status = '1';
            } else {
                $l_status = '0';
            }

            // Port sync
            if ($l_sync_port) {
                $l_data = [
                    'title'      => $l_row['DESCRIPTION'],
                    //'Port ' . $l_hw_key,
                    'port_type'  => $l_row['TYPE'],
                    'speed'      => isys_convert::speed($l_speed, $l_speed_type),
                    'speed_type' => $l_speed_type_id,
                    'mac'        => $l_row['MACADDR'],
                    'active'     => $l_status
                ];

                // Interface to port.
                if (isset($l_interface_id)) {
                    $l_data['interface'] = $l_interface_id;
                }

                // Add hostaddress.
                if (!$l_sync_ip) {
                    $l_data['addresses'] = [
                        $l_ip_id
                    ];
                }

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title'      => ['value' => $l_row['DESCRIPTION']],
                        // 'Port ' . $l_hw_key),
                        'port_type'  => ['title_lang' => $l_row['TYPE']],
                        'speed'      => ['value' => $l_speed],
                        'speed_type' => ['title_lang' => $l_speed_type],
                        'mac'        => ['value' => $l_row['MACADDR']],
                        'active'     => [
                            'value' => ($l_status) ? isys_application::instance()->container->get('language')
                                ->get('LC__UNIVERSAL__YES') : isys_application::instance()->container->get('language')
                                ->get('LC__UNIVERSAL__NO')
                        ],
                        'interface'  => ['value' => $l_row['DESCRIPTION']]
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

            // Ip sync
            if ($l_sync_ip) {
                $l_subnet = null;
                $l_dhcp = false;

                // ip type check ipv4, ipv6
                if (Ip::ip2long($l_row['IPSUBNET']) > 0 && Ip::ip2long($l_row['IPADDRESS']) > 0) {
                    $l_subnet = $l_row['IPSUBNET'];
                    $l_dhcp = false;
                    $l_address = $l_row['IPADDRESS'];
                } elseif (Ip::ip2long($l_row['IPDHCP']) > 0) {
                    $l_subnet = $l_row['IPSUBNET'];
                    $l_dhcp = true;
                    $l_address = $l_row['IPDHCP'];
                } elseif ($l_row['IPADDRESS'] != '' && $l_row['IPMASK'] != '') {
                    // Calculate net ip
                    $l_subnet = Ip::validate_net_ip($l_row['IPADDRESS'], $l_row['IPMASK'], null, true);
                    $l_dhcp = false;
                    $l_address = $l_row['IPADDRESS'];
                }

                // Secondary Check
                if ($l_address !== null && $l_subnet !== null && !in_array($l_address, $l_already_imported_ips)) {
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
                            $l_ip_type = defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2);
                        }
                        $l_net_id = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');
                    }

                    // Check Net
                    $availableNetsKey = $l_subnet . '|' . $l_cidr_suffix;
                    if (isset($availableNets[$availableNetsKey])) {
                        $l_net_id = $availableNets[$availableNetsKey]['row_data']['isys_cats_net_list__isys_obj__id'];
                        $l_net_title = $availableNets[$availableNetsKey]['row_data']['isys_obj__title'];

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
                        $l_net_title = $l_subnet;
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
                        'net_type'   => $l_net_type,
                        'primary'    => $l_status,
                        'active'     => '1',
                        'net'        => $l_net_id,
                        'hostname'   => '',
                        'domain'     => '',
                        'dns_domain' => ''
                    ];

                    if ($l_status) {
                        $l_data['hostname'] = $deviceTitle;

                        // It is possible that the domain is in the device title
                        if (strpos($deviceTitle, '.')) {
                            $l_domain = explode('.', $deviceTitle);
                            $l_data['hostname'] = array_shift($l_domain);

                            $l_data['domain'] = implode('.', $l_domain);

                            if ($l_data['domain']) {
                                $l_data['dns_domain'] = isys_cmdb_dao_dialog::instance($this->container->database)
                                        ->check_dialog('isys_net_dns_domain', $l_data['domain']) . ',';
                            }
                        }

                        if ($l_row['domain']) {
                            if (empty($l_data['domain'])) {
                                $l_data['domain'] = $l_row['domain'];
                            }

                            $l_data['dns_domain'] .= $l_row['domain'];
                        }

                        $l_data['dns_domain'] = rtrim($l_data['dns_domain'], ',');
                    }

                    if ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                        $l_data['ipv4_address'] = $l_address;
                        $l_data['ipv4_assignment'] = $l_ip_type;
                    } elseif ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                        $l_data['ipv6_address'] = $l_address;
                        $l_data['ipv6_assignment'] = $l_ip_type;
                    }

                    // add port
                    if ($l_port_id > 0) {
                        $l_data['assigned_port'] = $l_port_id;
                    } else {
                        $l_data['assigned_port'] = null;
                    }

                    if ($l_status) {
                        if ($l_row['domain']) {
                            $l_data['dns_domain'] = $l_row['domain'] . ',';
                        }

                        if (strpos($deviceTitle, '.') !== false) {
                            $l_data['dns_domain'] .= isys_cmdb_dao_dialog::instance($this->container->database)
                                    ->check_dialog('isys_net_dns_domain', substr($deviceTitle, strpos($deviceTitle, '.') + 1)) . ',';
                        }
                        $l_data['dns_domain'] = rtrim($l_data['dns_domain'], ',');
                    }

                    if ($logbookActive) {
                        if ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                            $l_ip_assignment = $ipDao->get_dialog('isys_ip_assignment', $l_ip_type)
                                ->get_row();
                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                'ipv4_address'    => ['value' => $l_row["IPADDRESS"]],
                                'ipv4_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                'net_type'        => ['title_lang' => 'IPv4']
                            ];
                        } elseif ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                            $l_ip_assignment = $ipDao->get_dialog('isys_ipv6_assignment', $l_ip_type)
                                ->get_row();
                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                'ipv6_address'    => ['value' => $l_row["IPADDRESS"]],
                                'ipv6_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                'net_type'        => ['title_lang' => 'IPv6'],
                            ];
                        }

                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['hostname'] = [
                            'value' => ($l_status) ? substr($deviceTitle, 0, strpos($deviceTitle, '.')) : ''
                        ];
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['net'] = ['value' => $l_net_id];
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['active'] = ['value' => $l_status];
                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['primary'] = ['value' => $l_status];

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
        }
        if (is_countable($l_check_iface) && count($l_check_iface) > 0 && 1 == 2) {
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
            $cableConDao = isys_cmdb_dao_cable_connection::instance($this->container->database);
            foreach ($l_check_port as $l_val) {
                $l_cableConID = $cableConDao->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                $cableConDao->delete_cable_connection($l_cableConID);
                $cableConDao->delete_connector($l_val["assigned_connector"]);
                $portDao->delete_entry($l_val['data_id'], 'isys_catg_port_list');

                if ($logbookActive) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_PURGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT', null);
                }
            }
        }

        if ($overwriteIpPorts && is_countable($l_check_ip) && count($l_check_ip) > 0) {
            $this->delete_entries_from_category($l_check_ip, $ipDao, 'isys_catg_ip_list', $objId, $objType, 'LC__CATG__IP_ADDRESS', $logbookActive);
        }
    }

    /**
     * @param                             $objId
     * @param                             $objType
     * @param                             $uiData
     * @param                             $connectionTypeTitle
     * @param bool                        $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleUi($objId, $objType, $uiData, $connectionTypeTitle, $logbookActive = false)
    {
        $l_check_data = [];
        $categoryDao = isys_cmdb_dao_category_g_ui::instance($this->container->database);

        $l_res_ui = $categoryDao->retrieve('SELECT isys_catg_ui_list__id, isys_catg_ui_list__title, isys_catg_ui_list__isys_catg_connector_list__id, ' .
            'isys_ui_plugtype__title ' . 'FROM isys_catg_ui_list ' .
            'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id ' .
            'LEFT JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id ' . 'WHERE isys_catg_ui_list__isys_obj__id = ' .
            $categoryDao->convert_sql_id($objId));
        $l_ui_amount = $l_res_ui->num_rows();
        $cableConDao = isys_cmdb_dao_cable_connection::instance($this->container->database);

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

            foreach ($uiData as $l_row) {
                // Check if data already exists in i-doit
                foreach ($l_check_data as $l_key => $l_value) {
                    if ($l_value['title'] == $l_row["CAPTION"] && $l_value['plug'] == $l_row['TYPE']) {
                        unset($l_check_data[$l_key]);
                        continue 2;
                    }
                }

                // Create new data
                $l_data = [
                    'title'       => $l_row["CAPTION"],
                    'plug'        => $l_row["TYPE"],
                    'type'        => $connectionTypeTitle,
                    'description' => $l_row['DESCRIPTION'],
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title' => ['value' => $l_row["CAPTION"]],
                        'plug'  => ['title_lang' => $l_row["TYPE"]],
                        'type'  => ['title_lang' => $connectionTypeTitle],
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);

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

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }
            foreach ($l_check_data as $l_val) {
                $l_cableConID = $cableConDao->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                $cableConDao->delete_cable_connection($l_cableConID);
                $cableConDao->delete_connector($l_val["assigned_connector"]);
                $categoryDao->delete_entry($l_val['data_id'], 'isys_catg_ui_list');

                if ($logbookActive) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_PURGED', "-modified from OCS-", $objId, $objType, 'LC__CMDB__CATG__UNIVERSAL_INTERFACE', null);
                }
            }
        } else {
            // create
            foreach ($uiData as $l_row) {
                $l_data = [
                    'title'       => $l_row["CAPTION"],
                    'plug'        => $l_row["TYPE"],
                    'type'        => $connectionTypeTitle,
                    'description' => $l_row['DESCRIPTION'],
                ];

                if ($logbookActive) {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                        'title' => ['value' => $l_row["CAPTION"]],
                        'plug'  => ['title_lang' => $l_row["TYPE"]],
                        'type'  => ['title_lang' => $connectionTypeTitle],
                    ];

                    $l_changes = $this->getLogbook()
                        ->prepare_changes($categoryDao, null, $l_category_values);

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

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
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
     * @param                               $objId
     * @param                               $objType
     * @param                               $storageData
     * @param                               $capacityUnit
     * @param bool                          $logbookActive
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handleStorage($objId, $objType, $storageData, $capacityUnit, $logbookActive = false)
    {
        $l_check_data = [];
        $categoryDao = isys_cmdb_dao_category_g_stor::instance($this->container->database);

        $l_res_stor = $categoryDao->retrieve('SELECT isys_catg_stor_list__title, isys_stor_manufacturer__title, isys_stor_model__title, ' .
            'isys_catg_stor_list__capacity, isys_catg_stor_list__id ' . 'FROM isys_catg_stor_list ' .
            'LEFT JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id ' .
            'LEFT JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id ' . 'WHERE isys_catg_stor_list__isys_obj__id = ' .
            $categoryDao->convert_sql_id($objId));

        $l_stor_amount = $l_res_stor->num_rows();

        if ($l_stor_amount > 0) {
            // Check, Delete, Create
            while ($l_row = $l_res_stor->get_row()) {
                $l_check_data[] = [
                    'data_id'      => $l_row['isys_catg_stor_list__id'],
                    'title'        => $l_row['isys_catg_stor_list__title'],
                    'manufacturer' => $l_row['isys_stor_manufacturer__title'],
                    'model'        => $l_row['isys_stor_model__title'],
                    'capacity'     => $l_row['isys_catg_stor_list__capacity']
                ];
            }

            foreach ($storageData as $l_row) {
                // Check if data already exists in i-doit
                foreach ($l_check_data as $l_key => $l_value) {
                    if ($l_value['title'] == $l_row["NAME"] && $l_value['manufacturer'] == $l_row['MANUFACTURER'] && $l_value['model'] == $l_row['MODEL'] &&
                        $l_value['capacity'] == isys_convert::memory($l_row['DISKSIZE'], $capacityUnit)) {
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
                        ->prepare_changes($categoryDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__UNIVERSAL__DEVICES', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }

            if (is_countable($l_check_data) && count($l_check_data) > 0) {
                $this->delete_entries_from_category($l_check_data, $categoryDao, 'isys_catg_stor_list', $objId, $objType, 'LC__UNIVERSAL__DEVICES', $logbookActive);
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
                        ->prepare_changes($categoryDao, null, $l_category_values);

                    if (is_countable($l_changes) && count($l_changes) > 0) {
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $objId, $objType, 'LC__UNIVERSAL__DEVICES', serialize($l_changes));
                    }
                }

                $l_import_data = $categoryDao->parse_import_array($l_data);
                $l_entry_id = $categoryDao->sync($l_import_data, $objId, isys_import_handler_cmdb::C__CREATE);

                // Emit category signal (afterCategoryEntrySave).
                $this->container->signals->emit(
                    "mod.cmdb.afterCategoryEntrySave",
                    $categoryDao,
                    $l_entry_id,
                    !!$l_entry_id,
                    $objId,
                    $l_import_data,
                    isset($l_changes) ? $l_changes : []
                );
            }
        }
    }
}
