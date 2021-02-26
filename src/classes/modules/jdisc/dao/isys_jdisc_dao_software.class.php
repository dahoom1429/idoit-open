<?php

/**
 * i-doit
 *
 * JDisc software DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_jdisc_dao_software extends isys_jdisc_dao_data
{
    /**
     * @note DS: SplFixedArray Accessors for self::$m_software_cache
     */
    const OBJ_ID         = 0;
    const OBJ_TITLE      = 1;
    const OBJ_SYSID      = 2;
    const OBJ_TYPE_ID    = 3;
    const OBJ_TYPE_TITLE = 4;
    const OBJ_TYPE_CONST = 5;

    /**
     * @const imported from jdisc software, not an i-doit one
     */
    const INSTANCE_TYPE_APPLICATION_SET = 2;

    /**
     * Array map of the net connectors
     *
     * @var array
     */
    private static $m_connector_map = null;

    /**
     * Array map of the net listeners
     *
     * @var array
     */
    private static $m_listener_map = null;

    /**
     * This array will cache found applications, so we can save database resources.
     *
     * @var  isys_array
     */
    private static $m_software_cache = null;

    /**
     * Software filter cache
     *
     * @var array
     */
    private static $m_software_filter_cache = [];

    /**
     * This array will cache NOT found applications, so we don't need to search for them over and over again.s
     *
     * @var  array
     */
    protected $m_missing_software_cache = [];

    /**
     * @var array
     */
    private $m_created_objects = [];

    /**
     * Cache net listener connections
     *
     * @var array
     */
    private $m_listener_connections = null; // function

    /**
     * @var array
     */
    private $m_software_license_cache = null;

    /**
     * Contains a filter which software are to be imported
     *
     * @var string
     */
    private $m_software_filter = null;

    /**
     * Determines if the filter is a 0 = whitelist or 1 = blacklist
     *
     * @var null
     */
    private $m_software_filter_type = null;

    /**
     * Determines if the filter is a 0 = whitelist or 1 = blacklist
     *
     * @var null
     */
    private $m_software_filter_type_regexp = null;

    /**
     * Determines which field should be prioritized for the object title for operating systems.
     *
     * @var bool
     */
    private $osFamilyAsObjectTitle = false;

    /**
     * Determines if the simple (new) database category model will be used
     *
     * @var bool
     */
    private $simpleDatabaseModel = false;

    /**
     * @param $value
     */
    public function setOsFamilyAsObjectTitle($value)
    {
        $this->osFamilyAsObjectTitle = (bool)$value;
    }

    /**
     * @param $value
     */
    public function setSimpleDatabaseModel($value)
    {
        $this->simpleDatabaseModel = (bool)$value;
    }

    /**
     * @return bool
     */
    public function isSimpleDatabaseModel()
    {
        return $this->simpleDatabaseModel;
    }

    /**
     * Method for counting all software-entries in JDisc.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function count_software()
    {
        // I use this instead of "COUNT(*)" because you can't group during counting.
        return $this->m_pdo->num_rows($this->fetch('SELECT name FROM application GROUP BY name;'));
    }

    /**
     * Method for receiving the operating system, assigned to the given device.
     * This method implements more logic than usual - Because we want to create operating systems with specific information (version, manufacturer, patchlevel, ...).
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     * @param   boolean $p_all_software If set to true we create objects for every software JDisc could find.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_os_by_device($p_id, $p_raw = false, $p_all_software = false, &$p_object_ids = [], &$p_connections = [], $p_import_software_licences = false)
    {
        $l_return = [];

        if (!defined('C__OBJTYPE__OPERATING_SYSTEM')) {
            return false;
        }

        // Now for the operating system(s).
        $l_already_imported = [];
        $l_device_assignments = new isys_array();
        $l_license_selection = '';

        $l_application_dao = isys_cmdb_dao_category_s_application::instance($this->m_db);

        // We don´t need to retrieve the assignments if we are in overwrite mode
        if (isys_jdisc_dao_data::clear_data() === false) {
            $l_device_assignments = $this->get_application_assignments(
                $this->get_current_object_id(),
                ' AND isys_catg_application_list__isys_catg_application_priority__id = ' . $this->convert_sql_id(defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY'))
            );
        }

        if ($p_import_software_licences) {
            $l_license_selection = ', TRIM(BOTH \' \' FROM os.productkey) AS lickey, os.licensetype AS lictype, os.licensestatus AS licstatus, TRIM(BOTH \' \' FROM os.licensecomment) AS licdescr';
        }

        $l_sql = 'SELECT os.*, TRIM(BOTH \' \' FROM LOWER(os.osversion)) AS lower_name, TRIM(BOTH \' \' FROM LOWER(os.osfamily)) AS lower_name2
            ' . $l_license_selection . '
            FROM operatingsystem AS os
			LEFT JOIN device AS d
			ON d.operatingsystemid = os.id
			WHERE d.id = ' . $this->convert_sql_id($p_id);

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' OS rows');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            if (!$l_row['osversion']) {
                if ($l_row['osfamily']) {
                    $l_row['osversion'] = $l_row['osfamily'];
                    $l_row['lower_name'] = $l_row['lower_name2'];
                } else {
                    continue;
                }
            }

            $objTitle = $l_row['osversion'];
            $version = '';
            $licenceTitle = $l_row['osversion'];

            // Determine if osfamily or osversion should be used for the object title
            if ($this->osFamilyAsObjectTitle) {
                $objTitle = $l_row['osfamily'];
                $l_row['lower_name'] = $l_row['lower_name2'];
                $version = ($l_row['osversion'] != $l_row['osfamily']) ? $l_row['osversion'] : '';
                $licenceTitle = $version;
            }

            $l_application = $this->does_software_exist_in_idoit($l_row['lower_name'], C__OBJTYPE__OPERATING_SYSTEM);

            if ($p_import_software_licences && $l_row['lickey'] != '' && !isset($this->m_software_license_cache[$p_id][$l_application[self::OBJ_ID]])) {
                $l_lic_data = new SplFixedArray(6);
                $l_lic_data[0] = $l_row['lickey'];
                $l_lic_data[1] = $l_row['lictype'];
                $l_lic_data[2] = $l_row['licstatus'];
                $l_lic_data[3] = $l_row['licexpdate'];
                $l_lic_data[4] = $l_row['licdescr'];
                $l_lic_data[5] = $licenceTitle;

                $this->m_software_license_cache[$p_id][$l_application[self::OBJ_ID]] = $l_lic_data;
            }

            /*
             * We skip the iteration if we already imported this software to this device,
             * or if we don't want to import software which does not already exist in i-doit.
             */
            if (empty($l_row['osversion']) || isset($l_already_imported[C__OBJTYPE__OPERATING_SYSTEM . '_' . $l_row['lower_name']]) || (!$p_all_software && !$l_application)) {
                continue;
            }

            if ($l_application && !$p_raw && isset($l_device_assignments[$l_application[self::OBJ_ID] . '_' . $version])) {
                // Application already assigned
                continue;
            }

            $l_already_imported[C__OBJTYPE__OPERATING_SYSTEM . '_' . $l_row['lower_name']] = true;

            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                // The OS does not exist - So we create it!
                if (!$l_application) {
                    // Unset the cache entry, when we create the software.
                    unset($this->m_missing_software_cache[$l_row['osversion']]);

                    // @todo Check for import-mode before blindly creating new objects!
                    $l_application = $this->create_object($objTitle, $l_row['lower_name'], C__OBJTYPE__OPERATING_SYSTEM, $l_row['id']);

                    if ($l_application[self::OBJ_ID] != '') {
                        parent::set_object_id($l_application[self::OBJ_ID]);
                        $l_description = '';
                        if (!empty($l_row['osfamily'])) {
                            $l_description .= isys_application::instance()->container->get('language')
                                    ->get('LC__CMDB__CATS__OPERATION_SYSTEM__FAMILY') . ': ' . $l_row['osfamily'] . "\n";
                        }
                        if (!empty($l_row['patchlevel'])) {
                            $l_description .= isys_application::instance()->container->get('language')
                                    ->get('LC__CMDB__CATS__OPERATION_SYSTEM__PATCH') . ': ' . $l_row['patchlevel'] . "\n";
                        }
                        if (!empty($l_row['systemtype'])) {
                            $l_description .= isys_application::instance()->container->get('language')
                                    ->get('LC__CMDB__CATS__OPERATION_SYSTEM__TYPE') . ': ' . $l_row['systemtype'] . "\n";
                        }
                        if (!empty($l_row['installdate'])) {
                            $l_description .= isys_application::instance()->container->get('language')
                                    ->get('LC__CMDB__CATS__OPERATION_SYSTEM__INSTALLDATE') . ': ' . date('d.m.Y', strtotime($l_row['installdate'])) . "\n";
                        }
                        // We add the special information to the OS.
                        $l_application_dao->create(
                            $l_application[self::OBJ_ID],
                            C__RECORD_STATUS__NORMAL,
                            $l_row['osfamily'] . ' - ' . $l_row['patchlevel'],
                            null,
                            '',
                            $l_description
                        );
                    }
                }

                // We always get an array therefore we check if the first key
                if ($l_application[self::OBJ_ID] == '') {
                    $this->m_log->warning('The operating system "' . $l_row['osversion'] . '" does not exist in i-doit and was not created.');
                } else {
                    $l_return[] = $this->prepare_application($l_application, $p_connections, null, $version, $l_row['patchlevel'], $l_row['lower_name']);
                }
            }
        }

        // Pass created objects back.
        $p_object_ids += $this->m_created_objects;

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CATG__OPERATING_SYSTEM'),
                'const'             => 'C__CATG__OPERATING_SYSTEM',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for receiving the software, assigned to a given device.
     * This method implements more logic than usual - Because we want to create applications and operating systems
     * with specific information (version, manufacturer, patchlevel, ...).
     *
     * @todo    handle the import of the software assignment in here instead of in the import handler
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     * @param   boolean $p_all_software If set to true we create objects for every software JDisc could find.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_software_by_device($p_id, $p_raw = false, $p_all_software = false, &$p_object_ids = [], &$p_connections = [], $p_import_software_licences = false, $importSoftwareServices = true)
    {
        $l_return = [];
        $l_already_imported = [];
        $l_app_id_arr = $l_dbms_arr = $l_db_schema_arr = [];
        $l_device_assignments = new isys_array();

        /**
         * Cache application assignments for current device
         * Only retrieve assignments if import mode is not in overwrite mode
         *
         * @note DS: this is saving several queries for each application per device
         */
        if (isys_jdisc_dao_data::clear_data() === false) {
            $l_device_assignments = $this->get_application_assignments($this->get_current_object_id());
        }

        /**
         * IDE typehinting helper.
         *
         * @var  $l_application_dao     isys_cmdb_dao_category_s_application
         * @var  $l_db_instance_dao     isys_cmdb_dao_category_s_database_instance
         */
        $l_application_dao = isys_cmdb_dao_category_s_application::instance($this->m_db);
        $l_db_instance_dao = isys_cmdb_dao_category_s_database_instance::instance($this->m_db);

        $l_props = $l_application_dao->get_properties_ng();
        $l_manufacturer_table = $l_props['manufacturer'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0];

        // DBMS
        $l_sql = 'SELECT ai.instancename,ai.id AS instance_id, app.*, TRIM(BOTH \' \' FROM LOWER(app.name)) AS lower_name, TRIM(BOTH \' \' FROM LOWER(ai.instancename)) AS lower_instancename
			FROM applicationinstance AS ai
			LEFT JOIN application AS app
			ON app.id = ai.applicationid
			LEFT JOIN operatingsystem AS os
			ON os.id = ai.operatingsystemid
			LEFT JOIN device AS d
			ON d.operatingsystemid = os.id
			WHERE ai.instancetype = 0 AND d.id = ' . $this->convert_sql_id($p_id);

        if ($this->m_software_filter !== null && is_array($this->m_software_filter)) {
            $l_sql .= $this->build_software_filter('app.name');
        }
        $l_res = $this->fetch($l_sql);
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' database schema rows');
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_dbms = null;
            $l_db_schema = null;
            $l_manufacturer_id = null;

            if ((!$l_row['name'] && $l_row['instancename']) || isset($l_app_id_arr[$l_row['id']])) {
                continue;
            }

            if ($l_row['name'] && defined('C__OBJTYPE__DBMS')) {
                $l_dbms = $this->does_software_exist_in_idoit($l_row['lower_name'], C__OBJTYPE__DBMS);

                /*
                 * We skip the iteration if we already imported this software to this device,
                 * or if we don't want to import software which does not already exist in i-doit.
                 */
                if (!isset($l_already_imported[C__OBJTYPE__DBMS . '_' . $l_row['lower_name'] . '_' . $l_row['version']]) || ($p_all_software && !$l_dbms)) {
                    $l_already_imported[C__OBJTYPE__DBMS . '_' . $l_row['lower_name'] . '_' . $l_row['version']] = true;
                    if (!$l_dbms) {
                        // Unset the cache entry, when we create the software.
                        unset($this->m_missing_software_cache[$l_row['name']]);
                        if (!empty($l_row['manufacturer'])) {
                            $l_manufacturer_id = $this->get_manufacturer($l_row['manufacturer'], $l_manufacturer_table);
                        }
                        // @todo Check for import-mode before blindly creating new objects!
                        $l_dbms = $this->create_object($l_row['name'], $l_row['lower_name'], C__OBJTYPE__DBMS, $l_row['id']);

                        if ($l_dbms[self::OBJ_ID] != '') {
                            parent::set_object_id($l_dbms[self::OBJ_ID]);
                            $l_dbms_arr[$l_row['id']] = [
                                'isys_obj__id'         => $l_dbms[self::OBJ_ID],
                                'isys_obj__title'      => $l_dbms[self::OBJ_TITLE],
                                'isys_obj_type__id'    => $l_dbms[self::OBJ_TYPE_ID],
                                'isys_obj_type__const' => 'C__OBJTYPE__DBMS',
                            ];
                            // Create specific category only if the object has been really created
                            if (isys_cmdb_dao_jdisc::object_created_in_current_session($l_dbms[self::OBJ_ID])) {
                                // Create specific category entry
                                $l_application_dao->create($l_dbms[self::OBJ_ID], C__RECORD_STATUS__NORMAL, null, $l_manufacturer_id, /*$l_row['version']*/
                                    null, null);
                            }
                        }
                    }
                }
            }

            // Database schema
            if ($l_row['instancename'] && defined('C__OBJTYPE__DATABASE_SCHEMA') && !$this->isSimpleDatabaseModel()) {
                $l_db_schema = $this->does_software_exist_in_idoit($l_row['lower_instancename'], C__OBJTYPE__DATABASE_SCHEMA);

                /*
                 * We skip the iteration if we already imported this software to this device,
                 * or if we don't want to import software which does not already exist in i-doit.
                 */
                if (!isset($l_already_imported[C__OBJTYPE__DATABASE_SCHEMA . '_' . $l_row['lower_instancename']]) || ($p_all_software && !$l_db_schema)) {
                    $l_already_imported[C__OBJTYPE__DATABASE_SCHEMA . '_' . $l_row['lower_instancename']] = true;
                    if (!$l_db_schema) {
                        // Unset the cache entry, when we create the software.
                        unset($this->m_missing_software_cache[$l_row['instancename']]);
                        $l_manufacturer_id = null;
                        // @todo Check for import-mode before blindly creating new objects!
                        $l_db_schema = $this->create_object($l_row['instancename'], $l_row['lower_instancename'], C__OBJTYPE__DATABASE_SCHEMA, $l_row['instance_id']);
                        if ($l_db_schema[self::OBJ_ID] != '') {
                            parent::set_object_id($l_db_schema[self::OBJ_ID]);
                            $l_db_schema_arr[$l_row['id']] = [
                                'isys_obj__id'         => $l_db_schema[self::OBJ_ID],
                                'isys_obj__title'      => $l_db_schema[self::OBJ_TITLE],
                                'isys_obj_type__id'    => $l_db_schema[self::OBJ_TYPE_ID],
                                'isys_obj_type__const' => 'C__OBJTYPE__DATABASE_SCHEMA',
                            ];
                        }
                    } else {
                        $l_db_schema_arr[$l_row['id']] = [
                            'isys_obj__id'         => $l_db_schema[self::OBJ_ID],
                            'isys_obj__title'      => $l_db_schema[self::OBJ_TITLE],
                            'isys_obj_type__id'    => $l_db_schema[self::OBJ_TYPE_ID],
                            'isys_obj_type__const' => 'C__OBJTYPE__DATABASE_SCHEMA',
                        ];
                    }
                }
            }

            if ($l_dbms && !$p_raw && isset($l_device_assignments[$l_dbms[self::OBJ_ID] . '_' . $l_row['version']])) {
                continue;
            }

            $l_app_id_arr[$l_row['id']] = true;

            // We always get an array therefore we check if the first key
            if ($l_dbms[0] == '') {
                $this->m_log->warning('The software "' . $l_row['name'] . '" does not exist in i-doit and was not created.');
            } else {
                $l_return[] = $this->prepare_application($l_dbms, $p_connections, $l_db_schema_arr[$l_row['id']], $l_row['version'], null, $l_row['lower_name']);
            }
        }

        /**
         * @note DS: Free pdo driver memory
         */
        $this->m_pdo->free_result($l_res);

        $l_already_imported = [];

        /*LEFT JOIN applicationinstance AS ai
            ON ai.applicationid = a.id*/

        $l_license_selection = '';
        $l_license_join = '';
        if ($p_import_software_licences) {
            $l_license_selection = ', TRIM(BOTH \' \' FROM sl.productkey) AS lickey, sl.licensetype AS lictype, sl.licensestatus AS licstatus,
                sl.expirationdate AS licexpdate, TRIM(BOTH \' \' FROM sl.comment) AS licdescr';
            $l_license_join = 'LEFT JOIN softwarelicense AS sl ON sl.id = aosr.softwarelicenseid';
        }

        $l_sql = 'SELECT a.*, TRIM(BOTH \' \' FROM LOWER(a.name)) AS lower_name ' . $l_license_selection . '
            FROM application AS a
			LEFT JOIN applicationinstance AS ai ON ai.applicationid = a.id
			LEFT JOIN applicationoperatingsystemrelation AS aosr ON aosr.applicationid = a.id
			' . $l_license_join . '
			LEFT JOIN operatingsystem AS os ON os.id = aosr.operatingsystemid
			LEFT JOIN device AS d ON d.operatingsystemid = os.id
			WHERE d.id = ' . $this->convert_sql_id($p_id) . ' AND (ai.instancetype IS NULL 
			OR ai.instancetype = ' . $this->convert_sql_int(self::INSTANCE_TYPE_APPLICATION_SET) . ')';

        if ($this->m_software_filter !== null && is_array($this->m_software_filter)) {
            $l_sql .= $this->build_software_filter('a.name');
        }
        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' software rows');
        if (defined('C__OBJTYPE__APPLICATION')) {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_lic_data = null;
                if (!$l_row['name'] || isset($l_app_id_arr[$l_row['id']])) {
                    continue;
                }

                $l_application = $this->does_software_exist_in_idoit($l_row['lower_name'], C__OBJTYPE__APPLICATION);

                if ($l_application && $p_import_software_licences && $l_row['lickey'] != '' &&
                    !isset($this->m_software_license_cache[$p_id][$l_application[self::OBJ_ID] . '__' . $l_row['version']])) {
                    $l_lic_data = new SplFixedArray(6);
                    $l_lic_data[0] = $l_row['lickey'];
                    $l_lic_data[1] = $l_row['lictype'];
                    $l_lic_data[2] = $l_row['licstatus'];
                    $l_lic_data[3] = $l_row['licexpdate'];
                    $l_lic_data[4] = $l_row['licdescr'];
                    $l_lic_data[5] = $l_row['name'];

                    $this->m_software_license_cache[$p_id][$l_application[self::OBJ_ID] . '__' . $l_row['version']] = $l_lic_data;
                }

                /*
                 * We skip the iteration if we already imported this software to this device,
                 * or if we don't want to import software which does not already exist in i-doit.
                 */
                if (isset($l_already_imported[C__OBJTYPE__APPLICATION . '_' . $l_row['lower_name'] . '_' . $l_row['version']]) || (!$p_all_software && !$l_application)) {
                    continue;
                }

                if ($l_application && !$p_raw && isset($l_device_assignments[$l_application[self::OBJ_ID] . '_' . $l_row['version']])) {
                    continue;
                }

                $l_already_imported[C__OBJTYPE__APPLICATION . '_' . $l_row['lower_name'] . '_' . $l_row['version']] = true;
                $l_app_id_arr[$l_row['id']] = true;

                if ($p_raw === true) {
                    $l_return[] = $l_row;
                } else {
                    // The application does not exist - So we create it!
                    if (!$l_application) {
                        // Unset the cache entry, when we create the software.
                        unset($this->m_missing_software_cache[$l_row['name']]);

                        $l_manufacturer_id = null;

                        // @todo Check for import-mode before blindly creating new objects!
                        $l_application = $this->create_object($l_row['name'], $l_row['lower_name'], C__OBJTYPE__APPLICATION, $l_row['id']);

                        if ($l_application[self::OBJ_ID] != '') {
                            parent::set_object_id($l_application[self::OBJ_ID]);
                            // If the manufacturer is not empty, we try to receive it's ID.
                            if (!empty($l_row['manufacturer'])) {
                                $l_manufacturer_id = $this->get_manufacturer($l_row['manufacturer'], $l_manufacturer_table);
                            }
                            // Create specific category only if the object has been really created
                            if (isys_cmdb_dao_jdisc::object_created_in_current_session($l_application[self::OBJ_ID])) {
                                // Create specific category entry
                                $l_application_dao->create($l_application[self::OBJ_ID], C__RECORD_STATUS__NORMAL, '', $l_manufacturer_id, $l_row['version'], '');
                            }
                        }
                    }

                    // We always get an array therefore we check if the first key
                    if ($l_application[self::OBJ_ID] == '') {
                        $this->m_log->warning('The software "' . $l_row['name'] . '" does not exist in i-doit and was not created.');
                    } else {
                        $l_return[] = $this->prepare_application($l_application, $p_connections, null, $l_row['version'], null, $l_row['lower_name']);
                    }
                }
            }
        }

        /**
         * @note DS: Free pdo driver memory
         */
        $this->m_pdo->free_result($l_res);

        // Now for the operating system(s).
        $l_already_imported = [];

        $l_license_selection = '';
        if ($p_import_software_licences) {
            $l_license_selection = ', TRIM(BOTH \' \' FROM os.productkey) AS lickey, os.licensetype AS lictype, os.licensestatus AS licstatus, TRIM(BOTH \' \' FROM os.licensecomment) AS licdescr';
        }

        $l_sql = 'SELECT os.*, TRIM(BOTH \' \' FROM LOWER(os.osversion)) AS lower_name, TRIM(BOTH \' \' FROM LOWER(os.osfamily)) AS lower_name2
          ' . $l_license_selection . '
          FROM operatingsystem AS os
			LEFT JOIN device AS d
			ON d.operatingsystemid = os.id
			WHERE d.id = ' . $this->convert_sql_id($p_id);

        if ($this->m_software_filter !== null && is_array($this->m_software_filter)) {
            $l_sql .= $this->build_software_filter('os.osversion');
        }
        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' OS rows');

        if (defined('C__OBJTYPE__OPERATING_SYSTEM')) {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                if (!$l_row['osversion']) {
                    if (isset($l_row['osfamily'])) {
                        $l_row['osversion'] = $l_row['osfamily'];
                        $l_row['lower_name'] = $l_row['lower_name2'];
                    } else {
                        continue;
                    }
                }

                $objTitle = $l_row['osversion'];
                $version = '';

                // Determine if osfamily or osversion should be used for the object title
                if ($this->osFamilyAsObjectTitle) {
                    $objTitle = $l_row['osfamily'];
                    $l_row['lower_name'] = $l_row['lower_name2'];
                    $version = ($l_row['osversion'] != $l_row['osfamily']) ? $l_row['osversion'] : '';
                }

                $l_application = $this->does_software_exist_in_idoit($l_row['lower_name'], C__OBJTYPE__OPERATING_SYSTEM);

                if ($p_import_software_licences && $l_row['lickey'] != '' && !isset($this->m_software_license_cache[$p_id][$l_application[self::OBJ_ID]])) {
                    $l_lic_data = new SplFixedArray(6);
                    $l_lic_data[0] = $l_row['lickey'];
                    $l_lic_data[1] = $l_row['lictype'];
                    $l_lic_data[2] = $l_row['licstatus'];
                    $l_lic_data[3] = $l_row['licexpdate'];
                    $l_lic_data[4] = $l_row['licdescr'];
                    $l_lic_data[5] = $version;

                    $this->m_software_license_cache[$p_id][$l_application[self::OBJ_ID]] = $l_lic_data;
                }

                /*
                 * We skip the iteration if we already imported this software to this device,
                 * or if we don't want to import software which does not already exist in i-doit.
                 */
                if (empty($l_row['osversion']) || isset($l_already_imported[C__OBJTYPE__OPERATING_SYSTEM . '_' . $l_row['lower_name']]) ||
                    (!$p_all_software && !$l_application)) {
                    continue;
                }

                if ($l_application && !$p_raw && isset($l_device_assignments[$l_application[self::OBJ_ID] . '_' . $version])) {
                    // Application already assigned
                    continue;
                }

                $l_already_imported[C__OBJTYPE__OPERATING_SYSTEM . '_' . $l_row['lower_name']] = true;

                if ($p_raw === true) {
                    $l_return[] = $l_row;
                } else {
                    // The OS does not exist - So we create it!
                    if (!$l_application) {
                        // Unset the cache entry, when we create the software.
                        unset($this->m_missing_software_cache[$l_row['osversion']]);

                        // @todo Check for import-mode before blindly creating new objects!
                        $l_application = $this->create_object($objTitle, $l_row['lower_name'], C__OBJTYPE__OPERATING_SYSTEM, $l_row['id']);

                        if ($l_application[self::OBJ_ID] != '') {
                            parent::set_object_id($l_application[self::OBJ_ID]);
                            $l_description = '';
                            if (!empty($l_row['osfamily'])) {
                                $l_description .= isys_application::instance()->container->get('language')
                                        ->get('LC__CMDB__CATS__OPERATION_SYSTEM__FAMILY') . ': ' . $l_row['osfamily'] . "\n";
                            }
                            if (!empty($l_row['systemtype'])) {
                                $l_description .= isys_application::instance()->container->get('language')
                                        ->get('LC__CMDB__CATS__OPERATION_SYSTEM__TYPE') . ': ' . $l_row['systemtype'] . "\n";
                            }
                            if (!empty($l_row['installdate'])) {
                                $l_description .= isys_application::instance()->container->get('language')
                                        ->get('LC__CMDB__CATS__OPERATION_SYSTEM__INSTALLDATE') . ': ' . date('d.m.Y', strtotime($l_row['installdate'])) . "\n";
                            }
                            // Create specific category only if the object has been really created
                            if (isys_cmdb_dao_jdisc::object_created_in_current_session($l_application[self::OBJ_ID])) {
                                // Create specific category entry
                                $l_application_dao->create(
                                    $l_application[self::OBJ_ID],
                                    C__RECORD_STATUS__NORMAL,
                                    $l_row['osfamily'] . ' - ' . $l_row['patchlevel'],
                                    null,
                                    '',
                                    $l_description
                                );
                            }
                        }
                    }

                    // We always get an array therefore we check if the first key
                    if ($l_application[self::OBJ_ID] == '') {
                        $this->m_log->warning('The operating system "' . $l_row['osversion'] . '" does not exist in i-doit and was not created.');
                    } else {
                        $l_return[] = $this->prepare_application($l_application, $p_connections, null, $version, $l_row['patchlevel'], $l_row['lower_name']);
                    }
                }
            }
        }

        /**
         * @note DS: Free pdo driver memory
         */
        $this->m_pdo->free_result($l_res);

        // Now for the services.
        $l_already_imported = [];

        try {
            $l_sql = 'SELECT s.*, TRIM(BOTH \' \' FROM LOWER(s.name)) AS lower_name, TRIM(BOTH \' \' FROM s.name) AS name FROM service AS s
				LEFT JOIN serviceoperatingsystemrelation AS sosr
				ON sosr.serviceid = s.id
				LEFT JOIN operatingsystem AS os
				ON os.id = sosr.operatingsystemid
				LEFT JOIN device AS d
				ON d.operatingsystemid = os.id
				WHERE d.id = ' . $this->convert_sql_id($p_id);

            if ($this->m_software_filter !== null && is_array($this->m_software_filter)) {
                $l_sql .= $this->build_software_filter('s.name');
            }
            $l_res = $this->fetch($l_sql);

            $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' OS rows');

            if (defined('C__OBJTYPE__SERVICE') && $importSoftwareServices === true) {
                while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                    if (!$l_row['name']) {
                        continue;
                    }

                    $l_service = $this->does_software_exist_in_idoit($l_row['lower_name'], C__OBJTYPE__SERVICE);

                    /*
                     * We skip the iteration if we already imported this software to this device,
                     * or if we don't want to import software which does not already exist in i-doit.
                     */
                    if (empty($l_row['name']) || isset($l_already_imported[C__OBJTYPE__SERVICE . '_' . $l_row['lower_name']]) || (!$p_all_software && !$l_service)) {
                        continue;
                    }

                    $l_already_imported[C__OBJTYPE__SERVICE . '_' . $l_row['lower_name']] = true;

                    if ($p_raw === true) {
                        $l_return[] = $l_row;
                    } else {
                        // The service does not exist - So we create it!
                        if (!$l_service) {
                            // Unset the cache entry, when we create the software.
                            unset($this->m_missing_software_cache[$l_row['name']]);

                            // @todo Check for import-mode before blindly creating new objects!
                            $l_service = $this->create_object($l_row['name'], $l_row['lower_name'], C__OBJTYPE__SERVICE, $l_row['id']);

                            if ($l_service[self::OBJ_ID] != '') {
                                parent::set_object_id($l_service[self::OBJ_ID]);
                                // Create specific category only if the object has been really created
                                if (isys_cmdb_dao_jdisc::object_created_in_current_session($l_service[self::OBJ_ID])) {
                                    // Create specific category entry
                                    $l_application_dao->create($l_service[self::OBJ_ID], C__RECORD_STATUS__NORMAL, null, null, '', '');
                                }
                            }
                        }

                        if (!$l_service || $l_service[self::OBJ_ID] == '') {
                            $this->m_log->warning('The service "' . $l_row['name'] . '" does not exist in i-doit and was not created.');
                        } else {
                            if (strpos($l_row['name'], '$') !== false) {
                                // We have a database instance
                                $l_instance_name = trim(substr($l_row['name'], strpos($l_row['name'], '$') + 1, strlen($l_row['name'])));
                                $l_lower_name = strtolower($l_instance_name);
                                $l_database_schema = null;

                                // @todo Check in jdisc database in applicationinstance
                                $l_sql_appid = 'SELECT applicationid FROM applicationinstance AS appi
									LEFT JOIN operatingsystem AS os
									ON os.id = appi.operatingsystemid
									LEFT JOIN device AS d
									ON d.operatingsystemid = os.id
									WHERE instancename = ' . $this->convert_sql_text($l_instance_name) . ' AND d.id = ' . $this->convert_sql_id($p_id);
                                $l_res_appid = $this->fetch($l_sql_appid);

                                if ($this->m_pdo->num_rows($l_res_appid) > 0) {
                                    $l_database_schema = $l_dbms_arr[array_pop($this->m_pdo->fetch_row_assoc($l_res_appid))];
                                }

                                $l_instance = $this->does_software_exist_in_idoit($l_lower_name, defined_or_default('C__OBJTYPE__DATABASE_INSTANCE'));

                                if (!$l_instance && defined('C__OBJTYPE__DATABASE_INSTANCE') && !$this->isSimpleDatabaseModel()) {
                                    // Create database instance
                                    // Unset the cache entry, when we create the software.
                                    unset($this->m_missing_software_cache[$l_instance_name]);

                                    // @todo Check for import-mode before blindly creating new objects!
                                    $l_service = $this->create_object($l_instance_name, $l_lower_name, C__OBJTYPE__DATABASE_INSTANCE, $l_row['id']);

                                    if ($l_service[self::OBJ_ID] != '' && $l_database_schema !== null) {
                                        parent::set_object_id($l_service[self::OBJ_ID]);
                                        if ($l_database_schema['isys_obj_type__id'] == defined_or_default('C__OBJTYPE__DATABASE_SCHEMA')) {
                                            // Create specific category only if the object has been really created
                                            if (isys_cmdb_dao_jdisc::object_created_in_current_session($l_service[self::OBJ_ID])) {
                                                // Create specific category entry
                                                $l_db_instance_dao->create(
                                                    $l_service[self::OBJ_ID],
                                                    '',
                                                    '',
                                                    '',
                                                    '',
                                                    [$l_database_schema['isys_obj__id']],
                                                    C__RECORD_STATUS__NORMAL
                                                );
                                            }
                                            $l_service['database_schema'] = $l_database_schema;
                                        } else {
                                            $this->m_log->warning('Could not assign Object ' . $l_database_schema['isys_obj__title'] . ' (' .
                                                isys_application::instance()->container->get('language')
                                                    ->get($l_database_schema['isys_obj_type__title']) . ') as database schema for application assignment ' . $l_row['name'] .
                                                '!');
                                        }
                                    }
                                }
                            }

                            if (!$l_service && !$p_raw) {
                                // Application already assigned
                                continue;
                            }

                            if ($l_device_assignments[$l_service[self::OBJ_ID] . '_']) {
                                continue;
                            }

                            $l_return[] = $this->prepare_application($l_service, $p_connections);
                        }
                    }
                }
            }

            /**
             * @note DS: Free pdo driver memory
             */
            $this->m_pdo->free_result($l_res);
        } catch (Exception $e) {
            $this->m_log->warning('Could not import services, please check if JDisc Version is >= 3.0');
        }

        /**
         * Free memory
         */
        unset($l_device_assignments);

        /**
         * Pass created objects back
         */
        $p_object_ids += $this->m_created_objects;

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__APPLICATION'),
                'const'             => 'C__CATG__APPLICATION',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
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
    public function prepare_application($p_data, &$p_connections = [], $p_database_schema = null, $p_version = null, $p_patch_level = null, $p_lower_title = null)
    {
        //$this->m_log->debug('>> Preparing import for "' . $p_data[self::OBJ_TITLE] . '" (type:'.$p_data[self::OBJ_TYPE_ID].')');

        // We should always have the application in our system by now!
        if (!empty($p_data)) {
            $p_connections[$p_data[self::OBJ_ID]]['properties'] = $l_properties['application'] = [
                'tag'      => 'application',
                'value'    => $p_data[self::OBJ_TITLE],
                'id'       => $p_data[self::OBJ_ID],
                'type'     => $p_data[self::OBJ_TYPE_CONST],
                'type_id'  => $p_data[self::OBJ_TYPE_ID],
                'sysid'    => $p_data[self::OBJ_SYSID],
                'lc_title' => isys_application::instance()->container->get('language')
                    ->get($p_data[self::OBJ_TYPE_TITLE]),
                'title'    => $p_data[self::OBJ_TITLE]
            ];

            if ($p_database_schema) {
                $l_properties['assigned_database_schema'] = [
                    'tag'   => 'assigned_database_schema',
                    'value' => $p_database_schema['isys_obj__id'],
                    'id'    => $p_database_schema['isys_obj__id']
                ];
            }

            if ($p_version !== null && $p_lower_title !== null) {
                $l_version_id = null;
                $l_properties['assigned_version'] = [
                    'id'        => $p_data[self::OBJ_ID],
                    'title'     => $p_data[self::OBJ_TITLE],
                    'sysid'     => $p_data[self::OBJ_SYSID],
                    'type'      => $p_data[self::OBJ_TYPE_ID],
                    'ref_id'    => $l_version_id,
                    'ref_title' => $p_version,
                    'ref_type'  => 'C__CATG__VERSION',
                    'hotfix'    => $p_patch_level,
                    'lc_title'  => isys_application::instance()->container->get('language')
                        ->get('LC__CATG__VERSION_TITLE')
                ];
            }

            $l_return = [
                'data_id'    => null,
                'properties' => $l_properties
            ];

            return $l_return;
        }

        return null;
    }

    /**
     * Method for finding software in idoit. The specific categories must be assigned to the software object.
     *
     * @param   string $p_name The name of the software.
     *
     * @return  mixed  May be an array or boolean false.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function does_software_exist_in_idoit($p_name, $p_objtype_id)
    {
        if (self::$m_software_cache === null) {
            self::$m_software_cache = new isys_array();

            $l_arr = filter_defined_constants([
                'C__CATS__APPLICATION',
                'C__CATS__SERVICE',
                'C__CATS__DATABASE_SCHEMA',
                'C__CATS__DBMS',
                'C__CATS__DATABASE_INSTANCE',
                'C__CATS__LICENCE',
                'C__CATS__OPERATING_SYSTEM'
            ]);

            $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj__sysid, isys_obj_type__id, isys_obj_type__title, isys_obj_type__const, CONCAT(isys_obj_type__id, \'_\', TRIM(LOWER(isys_obj__title))) FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE isys_obj__title != \'\' AND isys_obj_type__isysgui_cats__id IN (' . implode(',', $l_arr) . ');';

            $l_query = $this->m_db->query($l_sql);
            while ($l_row = $this->m_db->fetch_row($l_query)) {
                if (isset($l_row[1]) && $l_row[1] && !isset(self::$m_software_cache[$l_row[6]])) {
                    /**
                     * @note DS: using SplFixedArray saves more than a half of memory..
                     */
                    self::$m_software_cache[$l_row[6]] = new SplFixedArray(6);
                    self::$m_software_cache[$l_row[6]][0] = (int)$l_row[0];
                    self::$m_software_cache[$l_row[6]][1] = $l_row[1];
                    self::$m_software_cache[$l_row[6]][2] = $l_row[2];
                    self::$m_software_cache[$l_row[6]][3] = (int)$l_row[3];
                    self::$m_software_cache[$l_row[6]][4] = $l_row[4];
                    self::$m_software_cache[$l_row[6]][5] = $l_row[5];
                }
            }
            $this->m_db->free_result($l_query);
        }

        return $p_name && isset(self::$m_software_cache[$p_objtype_id . '_' . $p_name]) ? self::$m_software_cache[$p_objtype_id . '_' . $p_name] : false;
    }

    /**
     * Return all application assignments for a specified object as array(array(objid => true))
     *
     * @param $p_object_id
     *
     * @return isys_array
     *
     * @author Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_application_assignments($p_object_id, $p_condition = '')
    {
        $l_apps = new isys_array();
        $l_query = 'SELECT isys_connection__isys_obj__id, isys_catg_version_list__title FROM isys_catg_application_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
			WHERE isys_catg_application_list__isys_obj__id = ' . (int)$p_object_id . ' ';
        if ($p_condition) {
            $l_query .= $p_condition;
        }
        $l_q = $this->m_db->query($l_query . ';');

        while ($l_row = $this->m_db->fetch_row($l_q)) {
            $l_apps[$l_row[0] . '_' . $l_row[1]] = true;
        }
        $this->m_db->free_result($l_q);
        unset($l_q);

        return $l_apps;
    }

    /**
     * Handle net listener category
     *
     * @param $p_id
     *
     * @return array
     */
    public function handle_net_listener($p_id, $p_object_id)
    {
        // Cache all listener ports of the current object
        $l_listener_map = $this->map_listener_ports($p_object_id, true);

        $l_sql = 'SELECT * FROM ((SELECT \'normal\' AS t, app.*, TRIM(BOTH \' \' FROM LOWER(app.name)) AS lower_name, app.id AS app_id, pc.fromport, pc.toport, UPPER(pct.name) AS protocol, pc.fromdeviceid AS deviceid, pc.todeviceid AS assigned_device, d1.name
			FROM portconnection AS pc
			LEFT JOIN applicationinstanceport AS aip
			ON aip.portconnectionid = pc.id
			LEFT JOIN applicationinstance AS ai
			ON aip.applicationinstanceid = ai.id
			LEFT JOIN application AS app
			ON app.id = ai.applicationid
			LEFT JOIN portconnectiontypelookup AS pct
			ON pct.id = pc.type
			LEFT JOIN device AS d1
			ON d1.id = pc.fromdeviceid
			WHERE (d1.id = ' . $this->convert_sql_id($p_id) . ' AND pc.fromport > 0))
UNION
(SELECT \'reverse\' AS t, app.*, TRIM(BOTH \' \' FROM LOWER(app.name)) AS lower_name, app.id AS app_id, pc.toport AS fromport, pc.fromport AS toport,  UPPER(pct.name) AS protocol, pc.todeviceid AS deviceid, pc.fromdeviceid AS assigned_device, d1.name
			FROM portconnection AS pc
			LEFT JOIN applicationinstanceport AS aip
			ON aip.portconnectionid = pc.id
			LEFT JOIN applicationinstance AS ai
			ON aip.applicationinstanceid = ai.id
			LEFT JOIN application AS app
			ON app.id = ai.applicationid
			LEFT JOIN portconnectiontypelookup AS pct
			ON pct.id = pc.type
			LEFT JOIN device AS d1
			ON d1.id = pc.todeviceid
			WHERE (d1.id = ' . $this->convert_sql_id($p_id) . ' AND pc.toport > 0))
        ) AS main ORDER BY fromport, toport';

        $l_res = $this->fetch($l_sql);
        $l_count = $this->m_pdo->num_rows($l_res);
        $this->m_log->debug('> Found ' . $l_count . ' net listener rows.');
        // Assign ports with no range
        if ($l_count > 0) {
            $this->handle_listener_helper($p_object_id, $l_res, $l_listener_map);
        }

        $this->m_pdo->free_result($l_res);
        unset($l_listener_map);

        return true;
    }

    /**
     * Create net connectors
     *
     * @param $p_jdisc_to_idoit
     *
     * @throws isys_exception_general
     */
    public function create_net_listener_connections($p_object_id, $p_device_id, $p_jdisc_to_idoit = null)
    {
        if (count($this->m_listener_connections) === 0) {
            return;
        }

        /**
         * @var $l_dao    isys_cmdb_dao_category_g_net_connector
         * @var $l_dao_ip isys_cmdb_dao_category_g_ip
         */
        $l_dao = isys_cmdb_dao_category_g_net_connector::instance($this->m_db);
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->m_db);

        self::$m_listener_map = new isys_array();
        self::$m_connector_map = new isys_array();

        foreach ($this->m_listener_connections as $l_device_id => $l_connections) {
            if (isys_module_jdisc::isNonExisting($l_device_id)) {
                continue;
            }

            $l_remote_port_ranges = $l_local_port_ranges = $l_ranges = [];
            // Retrieve the from object id
            if ($p_device_id == $l_device_id) {
                $l_from_object_id = $p_object_id;
            } elseif ($l_from_object_id = isys_jdisc_dao_matching::instance()
                ->get_object_id_by_device_id($l_device_id)) {
                $this->m_log->debug('Found device id ' . $l_device_id . ' via object matching while creating net listener connections.');
            } else {
                $this->m_log->debug('Device id ' . $l_device_id . ' not found in i-doit. Skipping creating net listener connections.');
                isys_module_jdisc::setNonExistingID($l_device_id);
                continue;
            }

            // Get primary ip address
            $l_primary_ip = $l_dao_ip->get_primary_ip($l_from_object_id)
                ->get_row_value('isys_cats_net_ip_addresses_list__id');

            $l_range_counter_local = $l_range_counter_remote = 0;

            foreach ($l_connections as $l_ports => $l_to_device_id) {
                if (isys_module_jdisc::isNonExisting($l_to_device_id)) {
                    continue;
                }

                list($l_from_port, $l_to_port, $l_protocol) = explode('_', $l_ports);
                $l_is_range = false;

                // Retrieve connected to object id
                if ($l_to_object_id = isys_jdisc_dao_matching::instance()
                    ->get_object_id_by_device_id($l_to_device_id)) {
                    $this->m_log->debug('Found device id ' . $l_to_device_id . ' via object matching while creating net listener connections.');
                } else {
                    $this->m_log->debug('Device id ' . $l_to_device_id . ' not found in i-doit. Skipping single net listener connection.');
                    isys_module_jdisc::setNonExistingID($l_to_device_id);
                    continue;
                }

                if ((isset($l_connections[$l_from_port . '_' . ((int)$l_to_port + 1) . '_' . $l_protocol]) &&
                        $l_connections[$l_from_port . '_' . ((int)$l_to_port + 1) . '_' . $l_protocol] == $l_to_device_id) ||
                    (isset($l_connections[$l_from_port . '_' . ((int)$l_to_port - 1) . '_' . $l_protocol]) &&
                        $l_connections[$l_from_port . '_' . ((int)$l_to_port - 1) . '_' . $l_protocol] == $l_to_device_id)) {
                    if (!isset($l_connections[$l_from_port . '_' . ((int)$l_to_port - 1) . '_' . $l_protocol])) {
                        $l_first_port_in_range = null;
                    } else {
                        $l_first_port_in_range = ($l_to_port -
                            (count($l_remote_port_ranges[$l_to_object_id][(string)$l_from_port . '_' . $l_protocol][$l_range_counter_remote])));
                    }

                    if ($l_remote_port_ranges[$l_to_object_id][(string)$l_from_port . '_' . $l_protocol][$l_range_counter_remote][0] != $l_first_port_in_range) {
                        $l_range_counter_remote++;
                    }

                    // Local port is connected to a remote port range
                    $l_remote_port_ranges[$l_to_object_id][(string)$l_from_port . '_' . $l_protocol][$l_range_counter_remote][] = $l_to_port;
                    $l_is_range = true;
                } elseif ((isset($l_connections[((int)$l_from_port + 1) . '_' . $l_to_port . '_' . $l_protocol]) &&
                        $l_connections[((int)$l_from_port + 1) . '_' . $l_to_port . '_' . $l_protocol] == $l_to_device_id) ||
                    (isset($l_connections[((int)$l_from_port - 1) . '_' . $l_to_port . '_' . $l_protocol]) &&
                        $l_connections[((int)$l_from_port - 1) . '_' . $l_to_port . '_' . $l_protocol] == $l_to_device_id)) {
                    if (!isset($l_connections[((int)$l_from_port - 1) . '_' . $l_to_port . '_' . $l_protocol])) {
                        $l_first_port_in_range = null;
                    } else {
                        $l_first_port_in_range = ($l_from_port -
                            (count($l_local_port_ranges[$l_to_object_id][(string)$l_to_port . '_' . $l_protocol][$l_range_counter_local])));
                    }

                    if ($l_local_port_ranges[$l_to_object_id][(string)$l_to_port . '_' . $l_protocol][$l_range_counter_local][0] != $l_first_port_in_range) {
                        $l_range_counter_local++;
                    }

                    // Local port range is connected to a remote port
                    $l_local_port_ranges[$l_to_object_id][(string)$l_to_port . '_' . $l_protocol][$l_range_counter_local][] = $l_from_port;
                    $l_is_range = true;
                }

                if ($l_is_range) {
                    // Ranges are being handled else where
                    continue;
                }

                // Map all port listeners of the assigned object
                if (!isset(self::$m_listener_map[$l_to_object_id])) {
                    $this->map_listener_ports($l_to_object_id);
                }

                // Skip connection because listener is not set on the connected object
                if (!isset(self::$m_listener_map[$l_to_object_id][$l_to_port . '_' . $l_protocol])) {
                    continue;
                }

                $l_listener = self::$m_listener_map[$l_to_object_id][$l_to_port . '_' . $l_protocol];

                // Map all port connectors of the current object
                if (!isset(self::$m_connector_map[$l_from_object_id])) {
                    $this->map_connector_ports($l_from_object_id);
                }

                if (isset(self::$m_connector_map[$l_from_object_id][$l_from_port . '_' . $l_listener . '_' . $l_protocol])) {
                    $l_id = self::$m_connector_map[$l_from_object_id][$l_from_port . '_' . $l_listener . '_' . $l_protocol];
                    // Update entry. Port connector exists
                    $l_arr = [
                        'ip_address'         => $l_primary_ip,
                        'port_from'          => $l_from_port,
                        'port_to'            => $l_from_port,
                        'connected_listener' => $l_listener,
                        'protocol'           => $this->get_net_protocol($l_protocol)
                    ];
                    $l_dao->save_data($l_id, $l_arr);
                } else {
                    // Create entry. Port connector does not exist
                    $l_arr = [
                        'isys_obj__id'       => $l_from_object_id,
                        'ip_address'         => $l_primary_ip,
                        'port_from'          => $l_from_port,
                        'port_to'            => $l_from_port,
                        'connected_listener' => $l_listener,
                        'protocol'           => $this->get_net_protocol($l_protocol)
                    ];

                    $l_dao->create_data($l_arr);
                }
            }

            // Create local ranges and assign to remote listener
            if (count($l_local_port_ranges) > 0) {
                foreach ($l_local_port_ranges as $l_remote_object_id => $l_ports) {
                    foreach ($l_ports as $l_remote_port => $l_port_ranges) {
                        $l_protocol = substr($l_remote_port, strpos($l_remote_port, '_') + 1);
                        $l_protocol_id = $this->get_net_protocol($l_protocol);

                        // Map all port listeners of the assigned object
                        if (!isset(self::$m_listener_map[$l_remote_object_id])) {
                            $this->map_listener_ports($l_remote_object_id);
                        }

                        // Skip connection because listener is not set on the connected object
                        if (!isset(self::$m_listener_map[$l_remote_object_id][$l_remote_port])) {
                            continue;
                        }

                        $l_listener = self::$m_listener_map[$l_remote_object_id][$l_remote_port];

                        // Map all port connectors of the current object
                        if (!isset(self::$m_connector_map[$l_from_object_id])) {
                            $this->map_connector_ports($l_from_object_id);
                        }

                        foreach ($l_port_ranges as $l_range) {
                            $l_from_port = $l_range[0];
                            $l_to_port = end($l_range);

                            if (isset(self::$m_connector_map[$l_from_object_id][$l_from_port . '_' . $l_listener . '_' . $l_protocol])) {
                                $l_id = self::$m_connector_map[$l_from_object_id][$l_from_port . '_' . $l_listener . '_' . $l_protocol];
                                // Update entry. Port connector exists
                                $l_arr = [
                                    'ip_address'         => $l_primary_ip,
                                    'port_from'          => $l_from_port,
                                    'port_to'            => $l_to_port,
                                    'connected_listener' => $l_listener,
                                    'protocol'           => $l_protocol_id
                                ];
                                if ($l_id) {
                                    $l_dao->save_data($l_id, $l_arr);
                                }
                            } else {
                                // Create entry. Port connector does not exist
                                $l_arr = [
                                    'isys_obj__id'       => $l_from_object_id,
                                    'ip_address'         => $l_primary_ip,
                                    'port_from'          => $l_from_port,
                                    'port_to'            => $l_to_port,
                                    'connected_listener' => $l_listener,
                                    'protocol'           => $l_protocol_id
                                ];

                                $l_dao->create_data($l_arr);
                            }
                        }
                    }
                }
            }

            // Create remote ranges to local listener
            if (count($l_remote_port_ranges) > 0) {
                foreach ($l_remote_port_ranges as $l_remote_object_id => $l_ports) {
                    foreach ($l_ports as $l_local_port => $l_port_ranges) {
                        $l_protocol = substr($l_local_port, strpos($l_local_port, '_') + 1);
                        $l_protocol_id = $this->get_net_protocol($l_protocol);

                        // Map all port connectors of the current object
                        if (!isset(self::$m_connector_map[$l_remote_object_id])) {
                            $this->map_connector_ports($l_remote_object_id);
                        }

                        // Map all port listeners of the assigned object
                        if (!isset(self::$m_listener_map[$l_from_object_id])) {
                            $this->map_listener_ports($l_from_object_id);
                        }

                        // Skip connection because listener is not set on the connected object
                        if (!isset(self::$m_listener_map[$l_from_object_id][$l_local_port])) {
                            continue;
                        }

                        $l_listener = self::$m_listener_map[$l_from_object_id][$l_local_port];

                        foreach ($l_port_ranges as $l_range) {
                            $l_from_port = $l_range[0];
                            $l_to_port = end($l_range);

                            if (isset(self::$m_connector_map[$l_remote_object_id][$l_from_port . '_' . $l_listener . '_' . $l_protocol])) {
                                $l_id = self::$m_connector_map[$l_remote_object_id][$l_from_port . '_' . $l_listener . '_' . $l_protocol];
                                if ($l_id) {
                                    // Update entry. Port connector exists
                                    $l_arr = [
                                        'ip_address'         => $l_primary_ip,
                                        'port_from'          => $l_from_port,
                                        'port_to'            => $l_to_port,
                                        'connected_listener' => $l_listener,
                                        'protocol'           => $l_protocol_id
                                    ];
                                    $l_dao->save_data($l_id, $l_arr);
                                }
                            } else {
                                // Create entry. Port connector does not exist
                                $l_arr = [
                                    'isys_obj__id'       => $l_from_object_id,
                                    'ip_address'         => $l_primary_ip,
                                    'port_from'          => $l_from_port,
                                    'port_to'            => $l_to_port,
                                    'connected_listener' => $l_listener,
                                    'protocol'           => $l_protocol_id
                                ];

                                $l_dao->create_data($l_arr);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Cache port relevant data into temporary table
     *
     * @param $p_id
     * @param $p_data
     * @param $p_type
     *
     * @return $this|void
     */
    public function cache_data($p_id, $p_data, $p_type)
    {
        if (count($this->m_listener_connections) > 0) {
            parent::cache_data($p_id, $this->m_listener_connections, self::C__CACHE__LISTENER);
            unset($this->m_listener_connections);
        }

        if (count($this->m_software_license_cache) > 0) {
            parent::cache_data($p_id, $this->m_software_license_cache, self::C__CACHE__SOFTARE_LICENSES);
            unset($this->m_software_license_cache);
        }
    }

    /**
     * Load relevant data from temporary table
     *
     * @param $p_obj_id
     *
     * @throws Exception
     */
    public function load_cache($p_obj_id, $p_type = null)
    {
        $l_res = parent::load_cache($p_obj_id, ' AND type IN (' . self::C__CACHE__LISTENER . ',' . self::C__CACHE__SOFTARE_LICENSES . ')');

        $this->m_listener_connections = new isys_array();
        $this->m_software_license_cache = new isys_array();

        if ($this->m_db->num_rows($l_res) > 0) {
            while ($l_row = $this->m_db->fetch_row($l_res)) {
                switch ($l_row[2]) {
                    case self::C__CACHE__LISTENER:
                        $this->m_listener_connections = isys_format_json::decode($l_row[1]);
                        break;
                    case self::C__CACHE__SOFTARE_LICENSES:
                        $this->m_software_license_cache = isys_format_json::decode($l_row[1]);
                        break;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Set Software filter
     *
     * @param $p_value
     * @param $p_type
     */
    public function set_software_filter($p_value, $p_type, $p_typeRegexp = null)
    {
        if ($p_typeRegexp === 1) {
            $this->m_software_filter = [$p_value];
            $this->m_software_filter_type_regexp = 1;
        } else {
            $this->m_software_filter = explode(',', strtolower(str_replace('*', '%', $p_value)));
            $this->m_software_filter_type_regexp = 0;
        }
        $this->m_software_filter_type = $p_type;
    }

    /**
     * Object creation helper, which attaches the created object to the software cache
     *
     * @param $p_title
     * @param $p_type_id
     * @param $p_jdisc_id
     *
     * @return mixed
     * @throws isys_exception_cmdb
     * @throws isys_exception_general
     *
     * @author Dennis Stücken <dstuecken@synetics.de>
     */
    private function create_object($p_title, $p_lower_title, $p_type_id, $p_jdisc_id)
    {
        $l_identifier = $p_type_id . '_' . $p_lower_title;
        if (isset(self::$m_software_cache[$l_identifier])) {
            return self::$m_software_cache[$l_identifier];
        }

        if ($p_title) {
            /**
             * @var $l_dao isys_cmdb_dao_jdisc
             */
            $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

            $this->m_log->debug('Creating software object ' . $p_title);

            $l_id = $l_dao->insert_new_obj(
                $p_type_id,
                false,
                $p_title,
                null,
                C__RECORD_STATUS__NORMAL,
                null,
                date("Y-m-d H:i:s"),
                true,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                'By JDisc import: application ID #' . $p_jdisc_id
            );

            if ($l_id > 0) {
                $this->m_created_objects[$l_id] = $l_id;
                /**
                 * @note DS: using SplFixedArray for memory savings
                 */
                self::$m_software_cache[$l_identifier] = new SplFixedArray(6);
                self::$m_software_cache[$l_identifier][0] = (int)$l_id;
                self::$m_software_cache[$l_identifier][1] = $p_title;
                self::$m_software_cache[$l_identifier][2] = $l_dao::get_last_sysid();
                self::$m_software_cache[$l_identifier][3] = (int)$p_type_id;
                self::$m_software_cache[$l_identifier][4] = '';
                self::$m_software_cache[$l_identifier][5] = $this->get_object_type_const($p_type_id);

                return self::$m_software_cache[$l_identifier];
            }
        }

        return [
            '',
            '',
            '',
            '',
            ''
        ];
    }

    /**
     * Method to build the software filter
     *
     * @param $p_field
     *
     * @return string
     */
    private function build_software_filter($p_field)
    {
        if (!isset(self::$m_software_filter_cache[$p_field])) {
            if ($this->m_software_filter_type_regexp === 1) {
                // using filter_type as regexp
                if ($this->m_software_filter_type > 0) {
                    // regexp blacklisted, substring with regexp should not match and return null
                    self::$m_software_filter_cache[$p_field] =
                        ' AND (substring(' . $p_field . ' from ' . $this->convert_sql_text($this->m_software_filter[0]) . ') IS NULL)';
                } else {
                    // regexp whitelisted, substring with regexp should match and return not null
                    self::$m_software_filter_cache[$p_field] =
                        ' AND (substring(' . $p_field . ' from ' . $this->convert_sql_text($this->m_software_filter[0]) . ') IS NOT NULL)';
                }
            } else {
                // using filter_type as exploded comma separated string array
                self::$m_software_filter_cache[$p_field] = ' AND (' . rtrim(rtrim(implode('', array_map(function ($l_val) use ($p_field) {
                    if ($this->m_software_filter_type > 0) {
                        return ' LOWER(' . $p_field . ') NOT LIKE ' . $this->convert_sql_text(trim($l_val)) . ' AND';
                    } else {
                        return ' LOWER(' . $p_field . ') LIKE ' . $this->convert_sql_text(trim($l_val)) . ' OR';
                    }
                }, $this->m_software_filter)), 'OR'), 'AND') . ')';
            }
        }

        return self::$m_software_filter_cache[$p_field];
    }

    /**
     * Method to retrieve the manufacturer id for applications
     *
     * @param $p_manufacturer
     * @param $p_manufacturer_table
     *
     * @return int
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    private function get_manufacturer($p_manufacturer, $p_manufacturer_table)
    {
        $dialogInstance = isys_factory_cmdb_dialog_dao::get_instance($p_manufacturer_table, $this->m_db);
        $l_manufacturer = $dialogInstance->get_data(null, $p_manufacturer);
        $l_manufacturer_id = $l_manufacturer[$p_manufacturer_table . '__id'];

        // The manufacturer does not exist - We create it.
        if ($l_manufacturer_id === null) {
            $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

            // Insert the new manufacturer to the corresponding table.
            $l_manufacturer_sql = 'INSERT INTO ' . $p_manufacturer_table . ' (' . $p_manufacturer_table . '__title, ' . $p_manufacturer_table . '__status)
				VALUES (' . $l_dao->convert_sql_text($p_manufacturer) . ', ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ');';
            $l_dao->update($l_manufacturer_sql);

            $l_manufacturer_id = $l_dao->get_last_insert_id();

            $data = [
                $p_manufacturer_table . '__id' => $l_manufacturer_id,
                $p_manufacturer_table . '__title' => $p_manufacturer,
                $p_manufacturer_table . '__status' => defined_or_default('C__RECORD_STATUS__NORMAL'),
                'title' => $p_manufacturer,
                'title_lower' => strtolower($p_manufacturer)
            ];

            $dialogInstance->appendToCache($l_manufacturer_id, $data);
        }

        return $l_manufacturer_id;
    }

    /**
     * Get net protocl for category net listener
     *
     * @param $p_title
     *
     * @return int
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    private function get_net_protocol($p_title)
    {
        $dialogInstance = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_net_protocol');
        $l_net_protocol = $dialogInstance->get_data(null, $p_title);
        $l_net_protocol_id = $l_net_protocol['isys_net_protocol__id'];

        if ($l_net_protocol_id === null) {
            $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

            // Insert the new manufacturer to the corresponding table.
            $l_sql = 'INSERT INTO isys_net_protocol (' . 'isys_net_protocol__title, isys_net_protocol__status)
				VALUES (' . $l_dao->convert_sql_text($p_title) . ', ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ');';
            $l_dao->update($l_sql);

            $l_net_protocol_id = $l_dao->get_last_insert_id();

            $data = [
                'isys_net_protocol__id' => $l_net_protocol_id,
                'isys_net_protocol__title' => $p_title,
                'isys_net_protocol__status' => defined_or_default('C__RECORD_STATUS__NORMAL'),
                'title' => $p_title,
                'title_lower' => strtolower($p_title)
            ];

            $dialogInstance->appendToCache($l_net_protocol_id, $data);
        }

        return $l_net_protocol_id;
    }

    /**
     * Helper method which creates the net listeners
     *
     * @param $p_object_id
     * @param $p_result
     * @param $p_already_imported
     * @param $p_listener_map
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_listener_helper($p_object_id, $p_result, $p_listener_map)
    {
        $l_logbook_entries_collection = $l_already_imported = [];
        $l_internal_counter = 0;

        while ($l_row = $this->m_pdo->fetch_row_assoc($p_result)) {
            // Skip entry because port is not defined
            if ($l_row['fromport'] === null) {
                continue;
            }

            $l_opened_by = null;

            if ($l_row['name']) {
                $l_application = null;
                // Retrieve the correct application
                if (defined('C__OBJTYPE__DBMS') && isset(self::$m_software_cache[C__OBJTYPE__DBMS . '_' . $l_row['lower_name']])) {
                    $l_application = self::$m_software_cache[C__OBJTYPE__DBMS . '_' . $l_row['lower_name']][self::OBJ_ID];
                } elseif (defined('C__OBJTYPE__APPLICATION') && isset(self::$m_software_cache[C__OBJTYPE__APPLICATION . '_' . $l_row['lower_name']])) {
                    $l_application = self::$m_software_cache[C__OBJTYPE__APPLICATION . '_' . $l_row['lower_name']][self::OBJ_ID];
                } elseif (defined('C__OBJTYPE__DATABASE_SCHEMA') && isset(self::$m_software_cache[C__OBJTYPE__DATABASE_SCHEMA . '_' . $l_row['lower_name']])) {
                    $l_application = self::$m_software_cache[C__OBJTYPE__DATABASE_SCHEMA . '_' . $l_row['lower_name']][self::OBJ_ID];
                } elseif (defined('C__OBJTYPE__SERVICE') && isset(self::$m_software_cache[C__OBJTYPE__SERVICE . '_' . $l_row['lower_name']])) {
                    $l_application = self::$m_software_cache[C__OBJTYPE__SERVICE . '_' . $l_row['lower_name']][self::OBJ_ID];
                }

                if ($l_application !== null) {
                    $l_opened_by = $l_application;
                }
            }

            if ($l_row['assigned_device'] !== null) {
                $this->m_listener_connections[$l_row['deviceid']][$l_row['fromport'] . '_' . $l_row['toport'] . '_' . $l_row['protocol']] = $l_row['assigned_device'];
            }

            // Skip the port which we already have
            if (isset($l_already_imported[$l_row['fromport'] . '_' . $l_row['protocol']]) || isset($p_listener_map[$l_row['fromport'] . '_' . $l_row['protocol']])) {
                continue;
            }

            $l_already_imported[$l_row['fromport'] . '_' . $l_row['protocol']] = true;

            $l_arr = [
                'isys_obj__id' => $p_object_id,
                'port_from'    => $l_row['fromport'],
                'port_to'      => $l_row['fromport']
            ];

            if ($l_row['protocol'] !== null) {
                $l_arr['protocol'] = $this->get_net_protocol($l_row['protocol']);
            }

            if ($l_opened_by !== null) {
                $l_arr['opened_by'] = $l_opened_by;
            }

            // Create Listener
            isys_cmdb_dao_category_g_net_listener::instance(isys_application::instance()->database)
                ->create_data($l_arr);

            $l_changes = [
                'isys_cmdb_dao_category_g_net_listener::port_from' => [
                    'from' => isys_tenantsettings::get('gui.empty_value', '-'),
                    'to'   => $l_row['fromport']
                ],
                'isys_cmdb_dao_category_g_net_listener::port_to'   => [
                    'from' => isys_tenantsettings::get('gui.empty_value', '-'),
                    'to'   => $l_row['fromport']
                ],
                'isys_cmdb_dao_category_g_net_listener::protocol'  => [
                    'from' => isys_tenantsettings::get('gui.empty_value', '-'),
                    'to'   => $l_row['protocol']
                ]
            ];

            $l_count_changes = 3;

            if ($l_opened_by) {
                $l_changes['isys_cmdb_dao_category_g_net_listener::opened_by'] = [
                    'from' => isys_tenantsettings::get('gui.empty_value', '-'),
                    'to'   => $l_opened_by[1]
                ];
                $l_count_changes++;
            }

            $l_serialized_changes = serialize($l_changes);

            $l_logbook_entries_collection[] = [
                'object_id'      => $p_object_id,
                'object_type_id' => $this->get_current_object_type_id(),
                'category'       => isys_application::instance()->container->get('language')
                    ->get('LC__CATG__NET_LISTENER'),
                'changes'        => $l_serialized_changes,
                'count_changes'  => $l_count_changes
            ];

            $l_internal_counter++;
        }

        if ($l_internal_counter > 0 && $l_internal_counter > isys_tenantsettings::get('logbook.changes.multivalue-threshold', 25)) {
            self::set_logbook_entries([
                'object_id'      => $p_object_id,
                'object_type_id' => $this->get_current_object_type_id(),
                'category'       => 'LC__CATG__NET_LISTENER',
                'changes'        => null,
                'count_changes'  => $l_internal_counter
            ]);
        } else {
            if (count($l_logbook_entries_collection)) {
                array_walk($l_logbook_entries_collection, function ($l_item) {
                    self::set_logbook_entries($l_item);
                });
            }
        }
    }

    /**
     * Map net listener
     *
     * @param $p_object_id
     */
    private function map_listener_ports($p_object_id, $p_return = false)
    {
        $l_sql = 'SELECT isys_catg_net_listener_list__id, isys_catg_net_listener_list__port_from, isys_net_protocol__title
			FROM isys_catg_net_listener_list
			INNER JOIN isys_net_protocol ON isys_net_protocol__id = isys_catg_net_listener_list__isys_net_protocol__id
			WHERE isys_catg_net_listener_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        $l_res = $this->m_db->query($l_sql);
        $l_return = true;

        if ($p_return) {
            $l_return = new isys_array();
            while ($l_row = $this->m_db->fetch_row($l_res)) {
                $l_return[$l_row[1] . '_' . $l_row[2]] = $l_row[0];
            }
        } else {
            self::$m_listener_map[$p_object_id] = new isys_array();
            while ($l_row = $this->m_db->fetch_row($l_res)) {
                self::$m_listener_map[$p_object_id][$l_row[1] . '_' . $l_row[2]] = $l_row[0];
            }
        }
        $this->m_db->free_result($l_res);

        return $l_return;
    }

    /**
     * Map net connector
     *
     * @param $p_object_id
     */
    private function map_connector_ports($p_object_id)
    {
        $l_sql = 'SELECT isys_catg_net_connector_list__port_from, isys_catg_net_connector_list__id, isys_catg_net_connector_list__isys_catg_net_listener_list__id, isys_net_protocol__title
			FROM isys_catg_net_connector_list
			INNER JOIN isys_catg_net_listener_list ON isys_catg_net_listener_list__id = isys_catg_net_connector_list__isys_catg_net_listener_list__id
			INNER JOIN isys_net_protocol ON isys_net_protocol__id = isys_catg_net_listener_list__isys_net_protocol__id
			WHERE isys_catg_net_connector_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        $l_res = $this->m_db->query($l_sql);
        self::$m_connector_map[$p_object_id] = new isys_array();
        while ($l_row = $this->m_db->fetch_row($l_res)) {
            self::$m_connector_map[$p_object_id][$l_row[0] . '_' . $l_row[2] . '_' . $l_row[3]] = $l_row[1];
        }
        $this->m_db->free_result($l_res);
    }

    /*
     * Function which creates / updates all software licenses
     */
    public function create_software_licenses()
    {
        $l_query = '(SELECT
            TRIM(BOTH \' \' FROM osversion) AS lictitle,
            TRIM(BOTH \' \' FROM productkey) AS lickey,
            licensetype AS lictype,
            licensestatus AS licstatus,
            licenseexpirationdate AS licexprdate,
            TRIM(BOTH \' \' FROM licensecomment) AS licdescr
            FROM operatingsystem
            WHERE productkey != \'\'
            GROUP BY productkey, osversion, licensetype, licensestatus, licenseexpirationdate, licensecomment)
            UNION
            (SELECT
            TRIM(BOTH \' \' FROM app.name) AS lictitle,
            TRIM(BOTH \' \' FROM sl.productkey) AS lickey,
            sl.licensetype AS lictype,
            sl.licensestatus AS licstatus,
            expirationdate AS licexprdate,
            TRIM(BOTH \' \' FROM sl.comment) AS licdescr
            FROM operatingsystem AS os
            INNER JOIN applicationoperatingsystemrelation AS aosr ON os.id = aosr.operatingsystemid
            INNER JOIN application AS app ON app.id = aosr.applicationid
            INNER JOIN softwarelicense AS sl ON sl.id = aosr.softwarelicenseid
            WHERE sl.productkey != \'\'
            GROUP BY sl.productkey, app.name, sl.licensetype, sl.licensestatus, expirationdate, sl.comment)';

        $l_res = $this->fetch($l_query);

        if ($this->m_pdo->num_rows($l_res)) {
            $l_licenseObjects = [];
            $l_licenseKeys = [];
            $l_dao = isys_cmdb_dao_category_s_lic::instance($this->m_db);
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_lic_obj_info = $this->retrieve_licence_object($l_dao, $l_row['lictitle'], $l_licenseObjects);
                $l_lic_obj_id = $l_lic_obj_info[0];

                $l_lictype_id = (($l_row['lictype'] == 3 || $l_row['lictype'] == 10) ? C__CATS__LICENCE_TYPE__VOLUME_LICENCE : C__CATS__LICENCE_TYPE__SINGLE_LICENCE);

                // Create new licence key
                $l_data = [
                    'isys_obj__id' => $l_lic_obj_id,
                    'key'          => $l_row['lickey'],
                    'type'         => $l_lictype_id,
                    'expire'       => $l_row['licexprdate'],
                    'amount'       => 1
                    //'description' => $l_row['licdescr']
                ];

                if (!$l_lic_obj_info[1]) {
                    // Check key
                    $l_sql_check = 'SELECT isys_cats_lic_list__id id FROM isys_cats_lic_list
                      WHERE isys_cats_lic_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_lic_obj_id) . ' AND
                      isys_cats_lic_list__key = ' . $l_dao->convert_sql_text($l_row['lickey']);

                    $l_lic_list_id = $l_dao->retrieve($l_sql_check)
                        ->get_row_value('id');
                    if (!$l_lic_list_id) {
                        $l_lic_list_id = $l_dao->create_data($l_data);
                        $l_licenseKeys[$l_lic_list_id] = 1;
                    } elseif (isset($l_licenseKeys[$l_lic_list_id])) {
                        $l_licenseKeys[$l_lic_list_id] = (int)$l_licenseKeys[$l_lic_list_id] + 1;
                    }
                } else {
                    $l_lic_list_id = $l_dao->create_data($l_data);
                    $l_licenseKeys[$l_lic_list_id] = 1;
                }
            }

            // Update amount for each license key
            foreach ($l_licenseKeys as $l_lic_list_id => $l_amount) {
                $l_update = 'UPDATE isys_cats_lic_list SET isys_cats_lic_list__amount = ' . $l_dao->convert_sql_int($l_amount) . '
                    WHERE isys_cats_lic_list__id = ' . $l_dao->convert_sql_id($l_lic_list_id);
                $l_dao->update($l_update);
            }
        }
    }

    /**
     * Helper method which retrieves the licence object ID or creates the licence object
     *
     * @param isys_cmdb_dao_category $p_dao
     * @param                        $p_title
     * @param                        $p_licence_objects
     *
     * @return SplFixedArray
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function retrieve_licence_object(isys_cmdb_dao_category $p_dao, $p_title, &$p_licence_objects)
    {
        /*
         * $l_return[0] = ObjectID
         * $l_return[1] = Flag if Object has been created or not
         */
        $l_return = new SplFixedArray(2);
        $l_return[1] = false;
        if (!isset($p_licence_objects[$p_title])) {
            $l_return[0] = defined('C__OBJTYPE__LICENCE') ? ($p_dao->get_obj_id_by_title($p_title, C__OBJTYPE__LICENCE) ?: $p_dao->get_obj_id_by_title(
                $p_title . ' (JDisc)',
                C__OBJTYPE__LICENCE
            )) : '';
            if (!$l_return[0]) {
                $l_return[0] = $p_dao->create_object($p_title . ' (JDisc)', defined_or_default('C__OBJTYPE__LICENCE'));
                $this->m_log->debug('> Creating Licence object: ' . $p_title . ' (JDisc)');
                $l_return[1] = true;
            } else {
                $this->m_log->debug('> Licence object found: ' . $p_title . ' (JDisc)');
            }
            $p_licence_objects[$p_title . ' (JDisc)'] = $l_return[0];
        } else {
            $l_return[0] = $p_licence_objects[$p_title];
            $this->m_log->debug('> Licence object found: ' . $p_title);
        }

        return $l_return;
    }

    /**
     * Create / Update software license assignments
     *
     * @param $p_object_id
     * @param $p_device_id
     *
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handle_software_licenses($p_object_id, $p_device_id)
    {
        if (isset($this->m_software_license_cache[$p_device_id])) {
            $l_dao = isys_cmdb_dao_category_s_lic::instance($this->m_db);
            $l_dao_app = isys_cmdb_dao_category_g_application::instance($this->m_db);
            $l_licence_objects = [];
            // License Types
            // 3 Multiseat license = Volumne License
            // 10 Volume license = Volumne License
            // others = Single License

            foreach ($this->m_software_license_cache[$p_device_id] as $l_app_obj_id => $l_licences_data) {
                /*
                 * [0] Licence key
                 * [1] Licence type
                 * [2] Licence status
                 * [3] Licence expire date
                 * [4] Licence assignment description
                 * [5] Licence title
                 */
                $l_lickey = $l_licences_data[0];
                $l_licdescr = $l_licences_data[4];
                $l_lictitle = $l_licences_data[5];
                $l_version = null;

                if (strpos($l_app_obj_id, '__')) {
                    list($l_new_app_obj_id, $l_version) = explode('__', $l_app_obj_id);
                    $l_app_obj_id = $l_new_app_obj_id;
                }

                $l_lic_obj_info = $this->retrieve_licence_object($l_dao, $l_lictitle, $l_licence_objects);
                $l_lic_obj_id = $l_lic_obj_info[0];
                $l_created = $l_lic_obj_info[1];

                if (!$l_lic_obj_id) {
                    continue;
                }

                // Check if key exists in licence
                $l_sql_check = 'SELECT isys_cats_lic_list__id AS id FROM isys_cats_lic_list WHERE
                  isys_cats_lic_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_lic_obj_id) . ' AND isys_cats_lic_list__key = ' . $l_dao->convert_sql_text($l_lickey);
                $l_lic_data = $l_dao->retrieve($l_sql_check)
                    ->get_row();
                $l_lic_list_id = $l_lic_data['id'];

                // check if its assigned to the application assignment
                $l_sql_check = 'SELECT
                    isys_catg_application_list__id id,
                    isys_catg_application_list__isys_cats_lic_list__id lic_id,
                    isys_catg_application_list__status status,
                    isys_catg_application_list__description descr,
                    isys_catg_application_list__isys_catg_application_priority__id priority,
                    isys_catg_application_list__isys_catg_application_type__id type,
                    isys_catg_application_list__isys_catg_version_list__id version,
                    isys_catg_application_list__isys_cats_app_variant_list__id variant,
                    isys_catg_application_list__bequest_nagios_services nagios,
                    isys_catg_application_list__isys_catg_relation_list__id relation
                    FROM isys_catg_application_list
                    INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                    LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                    WHERE
                    isys_connection__isys_obj__id = ' . $l_dao->convert_sql_id($l_app_obj_id) . ' AND
                    isys_catg_application_list__isys_obj__id = ' . $l_dao->convert_sql_id($p_object_id);

                if (!empty($l_version)) {
                    $l_sql_check .= ' AND isys_catg_version_list__title = ' . $l_dao->convert_sql_text($l_version);
                }

                $l_assignment = $l_dao->retrieve($l_sql_check)
                    ->get_row();

                if ($l_assignment) {
                    if (($l_assignment['lic_id'] != $l_lic_list_id) || $l_created) {
                        $l_it_services = $l_dao_app->get_assigned_it_services($l_assignment['relation']);

                        $l_rel_obj = $l_dao->retrieve('SELECT isys_catg_relation_list__isys_obj__id id FROM isys_catg_relation_list
                            WHERE isys_catg_relation_list__id = ' . $l_dao->convert_sql_id($l_assignment['relation']))
                            ->get_row_value('id');

                        $l_database_schema = $l_dao->retrieve('SELECT isys_cats_database_access_list__isys_obj__id AS id
                            FROM isys_cats_database_access_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                            WHERE isys_connection__isys_obj__id = ' . $l_dao->convert_sql_id($l_rel_obj))
                            ->get_row_value('id');

                        if ($l_licdescr != '') {
                            if (strpos($l_assignment['descr'], 'License:') === false) {
                                $l_assignment['descr'] .= "\nLicense: " . $l_licdescr;
                            } else {
                                $l_assignment['descr'] = substr($l_assignment['descr'], 0, strpos($l_assignment['descr'], 'License:')) . "\n License: " . $l_licdescr;
                            }
                        }

                        $l_dao_app->save(
                            $l_assignment['id'],
                            $l_assignment['status'],
                            $l_app_obj_id,
                            $l_assignment['descr'],
                            $l_lic_list_id,
                            $l_database_schema,
                            $l_it_services,
                            $l_assignment["variant"],
                            $l_assignment["nagios"],
                            $l_assignment['type'],
                            $l_assignment['priority'],
                            $l_assignment['version']
                        );
                    }
                }
            }
        }
    }
}
