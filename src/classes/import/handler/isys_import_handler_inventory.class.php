<?php

use idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Mac;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ObjectTitle;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ModelSerial;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Hostname;
use idoit\Module\Cmdb\Model\Matcher\Ci\CiMatcher;
use idoit\Module\Cmdb\Model\Matcher\MatchConfig;

/**
 * @package     i-doit
 * @subpackage
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_import_handler_inventory extends isys_import_handler
{
    /**
     * Parses an hinventory export.
     * Sets:
     *  $this->m_data
     *  $this->m_hostname
     *  $this->m_scantime
     *
     * @param string $p_xml_data
     *
     * @return array|false
     */
    public function parse($p_xml_data = null)
    {
        if (empty($p_xml_data)) {
            $l_xml_data = $this->get_xml_data();
        } else {
            $l_xml_data = $p_xml_data;
        }

        $l_cat_id = null;

        $l_computer = $l_xml_data['computer'];
        unset($l_xml_data);

        $this->m_hostname = $l_computer['hostname'];
        $this->m_scantime = $l_computer['datetime'];

        if (strstr($this->m_scantime, "/")) {
            $l_scantmp_1 = explode(" ", $this->m_scantime);
            $l_date = $l_scantmp_1[0];
            $l_time = $l_scantmp_1[1];

            $l_scantmp_2 = explode("/", $l_date);

            $this->m_scantime = $l_scantmp_2[0] . '.' . $l_scantmp_2[1] . '.' . $l_scantmp_2[2] . ' ' . $l_time;
        }

        if (is_countable($l_computer) && count($l_computer) > 0) {
            foreach ($l_computer as $l_key => $l_value) {
                if (is_array($l_value)) {
                    foreach ($l_value as $l_child) {
                        if ($l_cat_id != $l_child['type']) {
                            $l_cat_id = $l_child['type'];
                        }

                        $l_fine = false;
                        $l_name = $l_child['name'];

                        unset($l_child['type'], $l_child['name']);

                        if (is_array($l_child)) {
                            $l_attribute = [];
                            $l_cat_id = strtolower($l_cat_id);

                            foreach ($l_child as $l_attributes) {
                                $l_attr = $l_attributes["attr"];

                                /* Format to lowercase for easier array handling */
                                $l_at_formatted = strtolower($l_attr["name"]);

                                if ($l_at_formatted === 'name') {
                                    $l_at_formatted = 'contact';
                                }

                                /* Add to data component */
                                if (array_key_exists($l_at_formatted, $l_attribute) && $l_cat_id === 'network adapter') {
                                    // Special case for network adapter.
                                    // Because it is possible to have more than one IP
                                    $l_puffer = $l_attribute[$l_at_formatted];
                                    unset($l_attribute[$l_at_formatted]);
                                    $l_attribute[$l_at_formatted][] = $l_attr["value"];
                                    if (is_array($l_puffer)) {
                                        $l_attribute[$l_at_formatted] = array_merge($l_attribute[$l_at_formatted], $l_puffer);
                                    } else {
                                        $l_attribute[$l_at_formatted][] = $l_puffer;
                                    }
                                } else {
                                    $l_attribute[$l_at_formatted] = $l_attr["value"];
                                }

                                unset($l_attr);
                                unset($l_at_formatted);
                            }

                            $l_attribute["name"] = $l_name;

                            /* Format h-inventory's clear-text assignments */
                            switch ($l_cat_id) {
                                case 'printer':
                                    $l_cat_id = defined_or_default('C__CATG__UNIVERSAL_INTERFACE');
                                    $l_fine = C__IMPORT__UI__PRINTER;
                                    break;

                                case 'pointing device':
                                    $l_cat_id = defined_or_default('C__CATG__UNIVERSAL_INTERFACE');
                                    $l_fine = C__IMPORT__UI__MOUSE;
                                    break;

                                case 'keyboard':
                                    $l_cat_id = defined_or_default('C__CATG__UNIVERSAL_INTERFACE');
                                    $l_fine = C__IMPORT__UI__KEYBOARD;
                                    $this->m_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][defined_or_default('C__CATS__CLIENT')]["keyboard"] = $l_attribute;
                                    break;

                                case 'desktop monitor':
                                    $l_cat_id = defined_or_default('C__CATG__UNIVERSAL_INTERFACE');
                                    $l_fine = C__IMPORT__UI__MONITOR;
                                    break;

                                case 'cpu':
                                //case 'physicalcpu':
                                    $l_cat_id = defined_or_default('C__CATG__CPU');
                                    break;

                                case 'battery':
                                    $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__MODEL')]['battery'] = $l_attribute;
                                    break;

                                case 'model':
                                    $l_cat_id = defined_or_default('C__CATG__MODEL');

                                    $this->m_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][defined_or_default('C__CATS__CLIENT')]['type'] = $l_attribute['systemtype'];

                                    $l_model = strtolower($l_attribute['model']);
                                    if (defined('C__CATG__VIRTUAL_MACHINE') &&
                                        (strstr($l_model, 'vmware') || strstr($l_model, 'virtual') || strstr($l_model, 'parallels') || strstr($l_model, 'innotek') ||
                                            strstr($l_model, 'qemu'))) {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__VIRTUAL_MACHINE')] = [
                                            'type' => $l_attribute['model']
                                        ];
                                    }

                                    if (!empty($l_bios)) {
                                        $l_attribute['bios'] = $l_bios;
                                    }
                                    // no break
                                case 'bios':
                                    if (is_array($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__MODEL')])) {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__MODEL')]["bios"] = $l_attribute;
                                    } else {
                                        $l_bios = $l_attribute;
                                    }
                                    break;

                                case 'physical memory':
                                    $l_cat_id = defined_or_default('C__CATG__MEMORY');
                                    break;

                                case 'graphic card':
                                    $l_cat_id = defined_or_default('C__CATG__GRAPHIC');
                                    break;

                                case 'cd/dvd drive':
                                    $l_fine = 'cd';
                                    $l_cat_id = defined_or_default('C__CATG__STORAGE_DEVICE');
                                    break;

                                case 'floppy':
                                    $l_fine = 'floppy';
                                    $l_cat_id = defined_or_default('C__CATG__STORAGE_DEVICE');
                                    break;

                                case 'hard disk':
                                    $l_fine = 'hd';
                                    $l_cat_id = defined_or_default('C__CATG__STORAGE_DEVICE');
                                    break;

                                case 'ide controller':
                                    $l_fine = 'IDE';
                                    $l_cat_id = defined_or_default('C__CATG__CONTROLLER');
                                    break;

                                case 'scsi controller':
                                    $l_fine = 'SCSI';
                                    $l_cat_id = defined_or_default('C__CATG__CONTROLLER');
                                    break;

                                case 'network adapter':
                                    $l_cat_id = defined_or_default('C__CATG__NETWORK');
                                    break;

                                case 'audio card':
                                    $l_cat_id = defined_or_default('C__CATG__SOUND');
                                    break;

                                case 'application':
                                    if (defined('C__OBJTYPE__APPLICATION') && defined('C__CATG__APPLICATION')) {
                                        $l_attribute["type"] = C__OBJTYPE__APPLICATION;
                                        $l_cat_id = C__CATG__APPLICATION;
                                    }
                                    break;

                                case 'operating system':
                                    if (defined('C__OBJTYPE__OPERATING_SYSTEM') && defined('C__CATG__OPERATING_SYSTEM')) {
                                        $l_attribute["type"] = C__OBJTYPE__OPERATING_SYSTEM;
                                        $l_cat_id = C__CATG__OPERATING_SYSTEM;
                                    }
                                    break;

                                case 'audit':
                                    if (strtolower($l_attribute["name"]) === 'loginuser') {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__LAST_LOGIN_USER')]["user"] = $l_attribute['contact'];
                                    } elseif (strtolower($l_attribute["name"]) === 'Uptime') {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__MODEL')]["uptime"] = $l_attribute;
                                    } elseif (strtolower($l_attribute["name"]) === 'ProductKey') {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__OPERATING_SYSTEM')]["licence"] = $l_attribute['serialnumber'];
                                    } elseif (strtolower($l_attribute["name"]) === 'ProductID') {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__OPERATING_SYSTEM')]["productid"] = $l_attribute['serialnumber'];
                                    } elseif (isset($l_attribute["filesystem"])) {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][defined_or_default('C__CATG__DRIVE')][] = $l_attribute;
                                    } else {
                                        $l_fine = false;
                                    }
                                    break;

                                default:
                                    $l_fine = false;
                                    break;
                            }

                            if (!is_array($l_attribute)) {
                                $l_attribute = [];
                            }

                            if (is_numeric($l_cat_id)) {
                                if ($l_fine) {
                                    $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_cat_id][$l_fine][] = $l_attribute;
                                } else {
                                    $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_cat_id][] = $l_attribute;
                                }
                            }

                            unset($l_attribute);
                        }
                    }
                }
            }
        } else {
            return false;
        }

        // CMDB specific workaround.
        $_POST[C__CMDB__GET__CATLEVEL] = -1;

        return $this->m_data;
    }

    /**
     * @var CiMatcher
     */
    private $m_ciMatcher = null;

    /**
     * Method for retrieving the object id
     *
     * @param           $p_objtype_id
     * @param string    $p_serial
     * @param string    $p_title
     * @param array     $p_macaddresses
     *
     * @return bool|int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function get_object_id_by_obj_match($p_objtype_id, $p_serial = null, $p_title = null, $p_macaddresses = [])
    {
        $l_dao_identifier = isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database);
        $l_objid = false;
        $l_keywords = [];

        $ignoredSerialStrings = [
            'systemserialnumber',
            'na',
            'tobefilled'
        ];

        // Remove all special characters and whitespaces
        $strippedSerial = trim(strtolower(preg_replace("/[^a-zA-Z0-9]|\s/", '', $p_serial)));
        $serialCheck = ($strippedSerial && (!in_array($strippedSerial, $ignoredSerialStrings) || false === stripos($strippedSerial, 'tobefilled')));

        if (!$l_objid) {
            if ($this->m_ciMatcher === null) {
                $this->m_ciMatcher = new CiMatcher(MatchConfig::factory(
                    isys_tenantsettings::get('import.hinventory.object-matching', 1),
                    isys_application::instance()->container
                ));
            }

            $l_condition = ' AND isys_obj__isys_obj_type__id = ' . $l_dao_identifier->convert_sql_id($p_objtype_id);

            if (count($p_macaddresses)) {
                foreach ($p_macaddresses as $l_mac) {
                    $l_keywords[] = new MatchKeyword(Mac::KEY, $l_mac, $l_condition);
                }
            }

            if ($serialCheck) {
                $l_keywords[] = new MatchKeyword(ModelSerial::KEY, $p_serial, $l_condition);
            }

            if ($p_title) {
                // Object title matcher.
                $l_keywords[] = new MatchKeyword(ObjectTitle::KEY, $p_title, $l_condition);

                // Hostname matcher.
                $l_keywords[] = new MatchKeyword(Hostname::KEY, $p_title, $l_condition);
            }

            if (count($l_keywords)) {
                $l_match = $this->m_ciMatcher->match($l_keywords);
                if ($l_match) {
                    $l_objid = $l_match->getId();
                }
            }
        }

        return $l_objid;
    }

    /**
     * Import
     *
     * @param   integer $p_objtype_id
     * @param   boolean $p_force_overwrite
     * @param   integer $p_object_id
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_cmdb
     */
    public function import($p_objtype_id, $p_force_overwrite = null, $p_object_id = null)
    {
        /**
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the request, if enabled via checkbox.
         */
        \idoit\Module\Cmdb\Search\Index\Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();

        $l_dao = new isys_cmdb_dao(isys_application::instance()->database);
        $l_em = isys_event_manager::getInstance();
        $l_serial = null;
        $l_mac_addresses = null;

        if (is_numeric($p_object_id) && $p_object_id > 0) {
            $l_objid = $p_object_id;
        } else {
            if (defined('C__CATG__MODEL') && isset($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__MODEL')]["bios"]["serialnumber"])) {
                $l_serial = $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__MODEL')]["bios"]["serialnumber"];
            }

            // Retrieve all MAC-Addresses
            $l_mac_addresses = $this->extract_unique_mac_address();
            $l_objid = $this->get_object_id_by_obj_match($p_objtype_id, $l_serial, $this->m_hostname, $l_mac_addresses);
        }

        /**
         * @desc starts the import, if scantime of current import is higher then the existing one,
         *         if nothing exists, a new object will be created, of course. ;)
         */
        if (!$this->check_scantime($l_objid, $this->m_scantime) || $p_force_overwrite) {
            if (!is_null($this->m_hostname)) {
                $this->m_log->debug("Importing host: " . $this->m_hostname);
            }
            $this->m_log->debug("Scantime: " . $this->m_scantime);

            if ($l_objid < 0 || empty($l_objid)) {
                $l_cmdb_status = isys_tenantsettings::get('import.hinventory.default_status', defined_or_default('C__CMDB_STATUS__IN_OPERATION'));

                $this->m_log->debug("Creating object..", true, "+");
                $l_objid = $l_dao->insert_new_obj(
                    $p_objtype_id,
                    false,
                    $this->m_hostname,
                    null,
                    C__RECORD_STATUS__BIRTH,
                    $this->m_hostname,
                    $this->m_scantime,
                    true,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $l_cmdb_status
                );

                if (method_exists($l_em, "triggerCMDBEvent")) {
                    $l_em->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECT_CREATED', "Inventory import", $l_objid, $p_objtype_id);
                }

                $this->m_log->debug("Created object-id: " . $l_objid);
            } else {
                if ($p_force_overwrite) {
                    $this->m_log->debug("Found obect-id: " . $l_objid);

                    $this->edit_scantime($l_objid, $this->m_scantime, $this->m_hostname);

                    if (method_exists($l_em, "triggerCMDBEvent")) {
                        $l_em->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECT_CHANGED', "Inventory update", $l_objid, $p_objtype_id, null, null, "Inventory");
                    }
                } else {
                    $this->m_log->debug("Import already existing and force-mode disabled! (Object-Id: {$l_objid}, Hostname: " . $this->m_hostname . ", Scantime: " .
                        $this->m_scantime . ")");

                    $this->m_log->debug("Try --force at the end of your parameter list.");

                    return false;
                }
            }

            if ($l_objid != -1) {
                $l_dao->set_object_status($l_objid, C__RECORD_STATUS__NORMAL);

                $l_dao->object_changed($l_objid);

                $this->m_log->debug("Category mode");
                $this->categorize($l_objid, "import", C__CMDB__CATEGORY__TYPE_GLOBAL);

                if ($this->m_data[C__CMDB__CATEGORY__TYPE_SPECIFIC]) {
                    $this->categorize($l_objid, "import", C__CMDB__CATEGORY__TYPE_SPECIFIC);
                }

                $this->m_log->debug("\n\n", true, "");

                if ($l_objid && $l_serial !== null) {
                    if ($l_serial !== 'To Be Filled By O.E.M.') {
                        // Add serial to category custom_identifier
                        isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                            ->set_identifier($l_objid, defined_or_default('C__CATG__IDENTIFIER_TYPE__H_INVENTORY'), 'serial', $l_serial, '', null, $this->m_scantime);
                    }
                }

                return true;
            }

            if (method_exists($l_em, 'triggerCMDBEvent')) {
                $l_em->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECT_CREATED__NOT', '');
            }

            throw new Exception("Could not create object");
        }

        $this->m_log->debug("Import already existing! (Object-Id: {$l_objid}, Hostname: " . $this->m_hostname . ", Scantime: " . $this->m_scantime . ")");

        return false;
    }

    /**
     * Retrieve all mac addresses
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function extract_unique_mac_address()
    {
        $l_return = [];
        if (defined('C__CATG__NETWORK') && isset($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK')]) &&
            is_countable($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK')]) &&
            count($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK')])) {
            foreach ($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK')] as $l_port) {
                if (!array_search($l_port['mac'], $l_return)) {
                    $l_return[] = $l_port['mac'];
                }
            }
        }

        return $l_return;
    }
}
