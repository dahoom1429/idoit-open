<?php

use idoit\Component\Property\Property;

/**
 * i-doit
 *
 * DAO: global category for operation systems.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @since       1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_operating_system extends isys_cmdb_dao_category_g_application
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'operating_system';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = false;

    /**
     * This variable holds the table name.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_application_list';

    /**
     * Dynamic property handling for displaying the operating system of the object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_application($p_row)
    {
        global $g_comp_database;

        $l_os = isys_cmdb_dao_category_g_operating_system::instance($g_comp_database)
            ->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_os && is_array($l_os)) {
            $l_quick_info = new isys_ajax_handler_quick_info();

            return $l_quick_info->get_quick_info($l_os["isys_catg_application_list__isys_obj__id"], $l_os['isys_obj__title'], C__LINK__OBJECT);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Executes the query to create the category entry for object referenced by $p_objID.
     *
     * @param integer $p_objID
     * @param integer $p_newRecStatus
     * @param integer $p_connectedObjID
     * @param string  $p_description
     * @param integer $p_licence
     * @param integer $p_database_schemata_obj
     * @param integer $p_it_service_obj
     * @param integer $p_variant
     * @param integer $p_bequest_nagios_services
     * @param integer $p_type
     * @param integer $p_priority
     * @param integer $p_version
     * @param array   $assignedDatabases
     *
     * @return  mixed  Integer with the newly created ID on success, otherwise boolean false.
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function create(
        $p_objID,
        $p_newRecStatus,
        $p_connectedObjID,
        $p_description,
        $p_licence = null,
        $p_database_schemata_obj = null,
        $p_it_service_obj = null,
        $p_variant = null,
        $p_bequest_nagios_services = 1,
        $p_type = null,
        $p_priority = null,
        $p_version = null,
        $assignedDatabases = null
    ) {
        if ($p_type === null && defined('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) {
            $p_type = C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM;
        }
        if ($p_priority === null && defined('C__CATG__APPLICATION_PRIORITY__PRIMARY')) {
            $p_priority = C__CATG__APPLICATION_PRIORITY__PRIMARY;
        }
        $l_return = parent::create(
            $p_objID,
            $p_newRecStatus,
            $p_connectedObjID,
            $p_description,
            $p_licence,
            $p_database_schemata_obj,
            $p_it_service_obj,
            $p_variant,
            $p_bequest_nagios_services,
            defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM'),
            defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY'),
            $p_version,
            $assignedDatabases
        );

        if (!$l_return) {
            return false;
        }

        // After saving, we go sure the current record is the only "primary" one.
        return ($this->make_primary_os($l_return, $p_objID) ? $l_return : false);
    }

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param  integer $p_obj_id
     *
     * @return integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = $p_obj_id ?: $this->m_object_id;

        if ($l_obj_id > 0) {
            $res = $this->get_data(null, $l_obj_id);
            return is_countable($res) && count($res);
        }

        return 0;
    }

    /**
     * Return Category Data - Note: Cannot use generic method because of the second left join.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= ' AND isys_catg_application_list__isys_catg_application_type__id = ' . $this->convert_sql_id(defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) .
            ' AND isys_catg_application_list__isys_catg_application_priority__id = ' . $this->convert_sql_id(defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY')) . ' ';

        return parent::get_data($p_catg_list_id, $p_obj_id, $p_condition, $p_filter, $p_status);
    }

    /**
     * This method needs to be overwritten to open the category in edit/view mode correctly.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_general_data($p_obj_id = null)
    {
        return $this->get_data(($p_obj_id ? null : ($_GET[C__CMDB__GET__OBJECT] ? null : false)), $p_obj_id ?: $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL)
            ->get_row();
    }

    /**
     * Callback method for property assigned_version.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function callback_property_assigned_version(isys_request $p_request)
    {
        global $g_comp_database;

        return isys_cmdb_dao_category_g_application::instance($g_comp_database)
            ->get_assigned_version($this->getCategoryEntryId($p_request->get_object_id()));
    }

    /**
     * Get category entry id
     *
     * @param int   $objectId
     *
     * @return int
     * @throws isys_exception_database
     */
    public function getCategoryEntryId($objectId)
    {
        return $this->retrieve(
            'SELECT isys_catg_application_list__id ' .
            'FROM isys_catg_application_list ' .
            'WHERE isys_catg_application_list__isys_catg_application_type__id = ' . $this->convert_sql_id(defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) . ' ' .
            'AND isys_catg_application_list__isys_catg_application_priority__id = ' . $this->convert_sql_id(defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY')) . ' ' .
            'AND isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($objectId)
        )->get_row_value('isys_catg_application_list__id');
    }

    /**
     * Import-Handler for this category.
     *
     * @param   array $p_data
     *
     * @return  array
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function import($p_data, $p_obj_id = null, $p_operating_system = false)
    {
        return parent::import($p_data, null, true);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        $l_properties = parent::properties();

        // @see  ID-7318  Do not translate the property here.
        $l_properties['application'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE] = 'LC__CATG__OPERATING_SYSTEM';
        $l_properties['application'][C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION] = 'The connected operating system';

        $l_properties['application'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] = idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
            'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                FROM isys_catg_application_list USE INDEX (os)
                    INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                    INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
            'isys_catg_application_list',
            'isys_catg_application_list__id',
            'isys_catg_application_list__isys_obj__id',
            '',
            '',
            idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                'isys_catg_application_list__isys_catg_application_type__id = \'' . defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM') . '\'',
                ' AND isys_catg_application_list__isys_catg_application_priority__id = \'' . defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY') . '\''
            ]),
            null,
            '',
            1
        );

        foreach ($l_properties as &$l_property) {
            $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] = str_replace(
                'C__CATG__APPLICATION',
                'C__CATG__OPERATING_SYSTEM',
                $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]
            );
            // @see ID-5826 Does it break anything if we don't set all properties "virtual"?
            // $l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] = true;
        }
        $l_properties['assigned_license'][C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CATG__OPERATING_SYSTEM_LICENSE';

        $l_properties['application'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__CAT_FILTER] = 'C__CATS__OPERATING_SYSTEM';
        $l_properties['application'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] = true;
        $l_properties['application'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__REPORT] = true;
        // $l_properties['application'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] = true; // @see ID-5826 ... Why was this set to virtual in the first place?
        $l_properties['description'][C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__OPERATING_SYSTEM', 'C__CATG__OPERATING_SYSTEM');

        // @see ID-5826 Set to virtual so that it does not appear in the validation GUI.
        $l_properties['application_priority'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] = true;

        /**
         * @var Property $propertyVariant
         * @var Property $propertyVersion
         */
        $propertyVariant = $l_properties['assigned_variant'];
        $propertyVersion = $l_properties['assigned_version'];

        $propertyVariant->mergePropertyData(
            [
                Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT (CASE WHEN isys_cats_app_variant_list__variant != \'\' THEN
                              CONCAT(isys_cats_app_variant_list__title, \' (\', isys_cats_app_variant_list__variant, \')\')
                              ELSE isys_cats_app_variant_list__title END)
                                FROM isys_catg_application_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                                INNER JOIN isys_cats_app_variant_list ON isys_cats_app_variant_list__isys_obj__id = isys_connection__isys_obj__id',
                    'isys_catg_application_list',
                    'isys_catg_application_list__id',
                    'isys_catg_application_list__isys_obj__id',
                    '',
                    '',
                    \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                        'isys_catg_application_list__isys_catg_application_type__id = \'' . defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM') . '\'',
                        ' AND isys_catg_application_list__isys_catg_application_priority__id = \'' . defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY') . '\'',
                        ' AND isys_catg_application_list__isys_cats_app_variant_list__id = isys_cats_app_variant_list__id'
                    ]),
                    null,
                    '',
                    1
                )
            ]
        );

        $propertyVersion->mergePropertyData(
            [
                Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_version_list__title
                                FROM isys_catg_application_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                                INNER JOIN isys_catg_version_list ON isys_catg_version_list__isys_obj__id = isys_connection__isys_obj__id',
                    'isys_catg_application_list',
                    'isys_catg_application_list__id',
                    'isys_catg_application_list__isys_obj__id',
                    '',
                    '',
                    \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                        'isys_catg_application_list__isys_catg_application_type__id = \'' . defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM') . '\'',
                        ' AND isys_catg_application_list__isys_catg_application_priority__id = \'' . defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY') . '\'',
                        ' AND isys_catg_application_list__isys_catg_version_list__id = isys_catg_version_list__id'
                    ]),
                    null,
                    '',
                    1
                )
            ]
        )->mergePropertyUiParams([
            'p_arData' => new isys_callback([
                'isys_cmdb_dao_category_g_operating_system',
                'callback_property_assigned_version',
            ])
        ]);

        $l_properties['assigned_version'] = $propertyVersion;

        unset($l_properties['assigned_database_schema'], $l_properties['bequest_nagios_services']);

        return $l_properties;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param integer $p_cat_level
     * @param integer $p_newRecStatus
     * @param integer $p_connectedObjID
     * @param string  $p_description
     * @param integer $p_licence
     * @param integer $p_database_schemata_obj
     * @param integer $p_it_service_obj
     * @param integer $p_variant
     * @param integer $p_bequest_nagios_services
     * @param integer $p_type
     * @param integer $p_priority
     * @param integer $p_version
     * @param array   $assignedDatabases
     *
     * @return  boolean
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_connectedObjID,
        $p_description,
        $p_licence,
        $p_database_schemata_obj,
        $p_it_service_obj,
        $p_variant = null,
        $p_bequest_nagios_services = null,
        $p_type = null,
        $p_priority = null,
        $p_version = null,
        $assignedDatabases = null
    ) {
        if ($p_type === null && defined('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) {
            $p_type = C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM;
        }
        if ($p_priority === null && defined('C__CATG__APPLICATION_PRIORITY__PRIMARY')) {
            $p_priority = C__CATG__APPLICATION_PRIORITY__PRIMARY;
        }
        $l_return = parent::save(
            $p_cat_level,
            $p_newRecStatus,
            $p_connectedObjID,
            $p_description,
            $p_licence,
            $p_database_schemata_obj,
            $p_it_service_obj,
            $p_variant,
            $p_bequest_nagios_services,
            defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM'),
            defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY'),
            $p_version,
            $assignedDatabases
        );

        if (!$l_return) {
            return false;
        }

        // After saving, we go sure the current record is the only "primary" one.
        return $this->make_primary_os($p_cat_level);
    }

    /**
     * Save global category application element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @throws  isys_exception_dao
     * @return  int|null
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_bRet = false;
        $l_intErrorCode = -1;

        $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

        $p_create = !is_countable($l_catdata) || count($l_catdata) === 0;

        if ($p_create) {
            // Overview page and no input was given
            if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && empty($_POST['C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN'])) {
                return null;
            }

            $l_applications = $_POST['C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN'];

            if (isys_format_json::is_json_array($l_applications)) {
                $l_applications = isys_format_json::decode($l_applications);
            }

            if (!is_array($l_applications)) {
                $l_applications = [$l_applications];
            }

            foreach ($l_applications as $l_application) {
                $l_id = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $l_application,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $_POST["C__CATG__OPERATING_SYSTEM_LICENSE__HIDDEN"],
                    $_POST["C__CATG__OPERATING_SYSTEM_DATABASE_SCHEMATA__HIDDEN"],
                    $_POST["C__CATG__OPERATING_SYSTEM_IT_SERVICE__HIDDEN"],
                    $_POST["C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT"] ?: -1,
                    $_POST["C__CATG__OPERATING_SYSTEM_BEQUEST_NAGIOS_SERVICES"],
                    defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM'),
                    defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY'),
                    $_POST['C__CATG__OPERATING_SYSTEM_VERSION'] ?: -1,
                    $_POST['C__CATG__APPLICATION_ASSIGNED_DATABASES__HIDDEN']
                );

                $this->m_strLogbookSQL = $this->get_last_query();

                if ($l_id) {
                    $l_catdata = ['isys_catg_application_list__id' => $l_id];
                    $l_bRet = true;
                    $p_cat_level = null;
                } else {
                    throw new isys_exception_dao("Could not create category element application");
                }
            }
        } else {
            $l_catdata = $l_catdata->get_row();
            $p_intOldRecStatus = $l_catdata["isys_catg_application_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_application_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CATG__OPERATING_SYSTEM_LICENSE__HIDDEN"],
                $_POST["C__CATG__OPERATING_SYSTEM_DATABASE_SCHEMATA__HIDDEN"],
                $_POST["C__CATG__OPERATING_SYSTEM_IT_SERVICE__HIDDEN"],
                $_POST["C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT"],
                $_POST["C__CATG__OPERATING_SYSTEM_BEQUEST_NAGIOS_SERVICES"],
                defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM'),
                defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY'),
                $_POST['C__CATG__OPERATING_SYSTEM_VERSION'],
                $_POST['C__CATG__APPLICATION_ASSIGNED_DATABASES__HIDDEN']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        if ($p_create) {
            return $l_catdata["isys_catg_application_list__id"];
        }

        return ($l_bRet == true) ? null : $l_intErrorCode;
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $p_category_data['properties']['application_type'][C__DATA__VALUE] = defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM');
        $p_category_data['properties']['application_priority'][C__DATA__VALUE] = defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY');

        // OS does not have any assigned schemas
        $p_category_data['properties']['assigned_database_schema'][C__DATA__VALUE] = null;

        if ($p_category_data['data_id'] === null) {
            $p_category_data['data_id'] = $this->get_data(null, $p_object_id)
                ->get_row_value('isys_catg_application_list__id');
        }

        return parent::sync($p_category_data, $p_object_id, $p_status);
    }

    /**
     * @param string $p_table
     * @param int   $p_obj_id
     *
     * @return int|bool
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        // Create connection dao
        $connectionDao = new isys_cmdb_dao_connection($this->get_database_component());

        $query = "INSERT INTO isys_catg_application_list (
                    isys_catg_application_list__isys_obj__id, 
                    isys_catg_application_list__isys_catg_application_type__id, 
                    isys_catg_application_list__status,
                    isys_catg_application_list__isys_catg_application_priority__id,
                    isys_catg_application_list__isys_connection__id) 
            VALUES
            (
              " . $this->convert_sql_id($p_obj_id) . ",
              " . $this->convert_sql_id(defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) . ",
              " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ",
              " . $this->convert_sql_int(defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY')) . ",
              " . $this->convert_sql_id($connectionDao->add_connection(null)) . "
            );";

        if ($this->update($query) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Compare category data
     *
     * We need to override this here to prevent execution
     * of inherit isys_cmdb_dao_category_g_application::compare_category_data
     * which tries to handle comparison procedure in multivalue mode
     *
     * @see ID-5371
     * @see isys_import_handler_cmdb::import_categories()
     *
     * @param array $p_category_data_values
     * @param array  $p_object_category_dataset
     * @param array  $p_used_properties
     * @param array  $p_comparison
     * @param int    $p_badness
     * @param int    $p_mode
     * @param int    $p_category_id
     * @param string $p_unit_key
     * @param array  $p_category_data_ids
     * @param mixed  $p_local_export
     * @param        $p_dataset_id_changed
     * @param        $p_dataset_id
     * @param        $p_logger
     * @param null   $p_category_name
     * @param null   $p_table
     * @param null   $p_cat_multi
     * @param null   $p_category_type_id
     * @param null   $p_category_ids
     * @param null   $p_object_ids
     * @param null   $p_already_used_data_ids
     */
    public function compare_category_data(
        &$p_category_data_values,
        &$p_object_category_dataset,
        &$p_used_properties,
        &$p_comparison,
        &$p_badness,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$p_dataset_id_changed,
        &$p_dataset_id,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    ) {
        // Check whether object has an entry in `operating system` category
        if (is_array($p_object_category_dataset) && !empty($p_object_category_dataset)) {

            // Collect necessary values
            $l_dataset = current($p_object_category_dataset);
            $l_dataset_id = $l_dataset[$p_table . '__id'];
            $l_dataset_key = key($p_object_category_dataset);

            // Check for differences of category dataset id
            if ($p_category_data_values['data_id'] !== $p_dataset_id) {

                // Set indicator that dateset id has changed
                $p_dataset_id_changed = true;

                // Set dataset id that matches our needs and going to be updated
                $p_dataset_id = $l_dataset_id;

                // Set comparison indicator to `match partially` and save candidate into reference
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $l_dataset_id;
            }
        }
    }

    /**
     * @param int    $objectId
     * @param string $categoryTable
     * @param bool   $hasRelation
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function clear_data($objectId, $categoryTable, $hasRelation = true)
    {
        $l_relation_dao = isys_cmdb_dao_category_g_relation::factory($this->get_database_component());

        if (!($applicationTypeId = defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM'))) {
            $applicationTypeId = $this->retrieve('SELECT isys_catg_application_type__id as id FROM 
                                                 isys_catg_application_type__const = ' . $this->convert_sql_text('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM') . ' LIMIT 1;')
                ->get_row_value('id');
        }

        if (!($applicationPriorityId = defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY'))) {
            $applicationPriorityId = $this->retrieve('SELECT isys_catg_application_priority__id as id FROM 
                                                 isys_catg_application_priority__const = ' . $this->convert_sql_text('C__CATG__APPLICATION_PRIORITY__PRIMARY') . ' LIMIT 1;')
                ->get_row_value('id');
        }

        if ($applicationTypeId > 0 && $applicationPriorityId > 0) {
            $applicationCondition = ' AND (isys_catg_application_list__isys_catg_application_type__id = ' . $this->convert_sql_id($applicationTypeId) . ' 
                AND isys_catg_application_list__isys_catg_application_priority__id = ' . $this->convert_sql_id($applicationPriorityId) . ')';

            $l_res = $this->retrieve('SELECT * FROM isys_catg_application_list 
                WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . $applicationCondition);

            while ($l_data = $l_res->get_row()) {
                if (isset($l_data["isys_catg_application_list__isys_catg_relation_list__id"])) {
                    $l_relation_dao->delete_relation($l_data["isys_catg_application_list__isys_catg_relation_list__id"]);
                } else {
                    continue;
                }
            }
            $l_res->free_result();

            $l_sql = 'DELETE FROM isys_catg_application_list WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . $applicationCondition;

            return $this->update($l_sql) && $this->apply_update();
        }
        return true;
    }
}
