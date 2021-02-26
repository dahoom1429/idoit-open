<?php

use idoit\Context\Context;
use idoit\Module\Cmdb\Interfaces\Printable;

/**
 * i-doit
 *
 * Export for CMDB objects
 *
 * @package     i-doit
 * @subpackage  Export CMDB
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_export_cmdb_object extends isys_export_cmdb
{
    /**
     * Some categories are empty by design. They should be skipped for an export
     *
     * @var   array
     * @todo  In betroffenen DAOs: leere arrays
     */
    private static $m_cat_skip = [
        C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
        C__CMDB__CATEGORY__TYPE_SPECIFIC => [],
        C__CMDB__CATEGORY__TYPE_CUSTOM   => []
    ];

    /**
     * Store, if skiped categories are already init
     * @var bool
     */
    private static $skipedCategoriesInit = false;

    /**
     * Counter for duplicated objects
     *
     * @var int
     */
    private static $m_duplicate_counter = 0;

    /**
     * Category types
     *
     * @var array
     */
    private $m_all_categories = [
        C__CMDB__CATEGORY__TYPE_GLOBAL,
        C__CMDB__CATEGORY__TYPE_SPECIFIC
    ];

    /**
     * @var bool
     */
    private $overwriteCmdbStatusOnMassChange = true;

    /**
     * Set overwrite cmdb status
     *
     * @param $value
     *
     * @return $this
     */
    public function setOverwriteCmdbStatusOnMassChange($value)
    {
        $this->overwriteCmdbStatusOnMassChange = (bool)$value;
        return $this;
    }

    /**
     * Initialize the skiped categories
     */
    private static function initSkippedCategories()
    {
        if (self::$skipedCategoriesInit) {
            return;
        }
        self::$skipedCategoriesInit = true;
        foreach (filter_defined_constants([
            'C__CATG__OVERVIEW',
            'C__CATG__VIRTUAL',
            'C__CATG__CABLING',
            'C__CATG__CLUSTER_ROOT',
            'C__CATG__VIRTUAL_HOST_ROOT',
            'C__CATG__VIRTUAL_MACHINE__ROOT',
            'C__CATG__NETWORK',
            // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW',
            'C__CATG__NETWORK_PORT_OVERVIEW',
            'C__CATG__LOGBOOK',
            'C__CATG__JDISC_DISCOVERY',
            'C__CATG__CMK_FOLDER',
            'C__CATG__NAGIOS_HOST_FOLDER',
            'C__CATG__NAGIOS_HOST_TPL_FOLDER',
            'C__CATG__NAGIOS_SERVICE_FOLDER',
            'C__CATG__NAGIOS_SERVICE_TPL_FOLDER',
            'C__CATG__LIVESTATUS',
        ]) as $categoryId) {
            self::$m_cat_skip[C__CMDB__CATEGORY__TYPE_GLOBAL][$categoryId] = true;
        }
    }

    /**
     * Gets all skipped categories
     *
     * @param $p_category_type
     *
     * @return mixed
     */
    public static function get_skipped_categories($p_category_type = null)
    {
        self::initSkippedCategories();
        if (is_int($p_category_type)) {
            return self::$m_cat_skip[$p_category_type];
        } else {
            return self::$m_cat_skip;
        }
    }

    /**
     * Creates a list of exportable categories (that are importable, too).
     *
     * @param int   $p_category_type (optional) Only create a list of categories
     *                               that matches this type.
     * @param array $p_match         (optional) Match list of exportable categories
     *                               against this list. It's an multi-dimensional assotiative array with
     *                               category type identifier as keys in the first dimension and category
     *                               identifier in the second.
     *
     * @return array Multi-dimensional, assotiative array of category types,
     * categories and their properties.
     */
    public static function fetch_exportable_categories($p_category_type = null, $p_match = null)
    {
        // Built-in short way:
        $p_exportable_categories = [];
        if (isset($GLOBALS['g_exportable_categories'])) {
            $p_exportable_categories = $GLOBALS['g_exportable_categories'];
        } else {
            $l_supported_types = [
                C__CMDB__CATEGORY__TYPE_GLOBAL,
                C__CMDB__CATEGORY__TYPE_SPECIFIC,
                C__CMDB__CATEGORY__TYPE_CUSTOM
            ];
            $l_types = [];
            if (isset($p_category_type)) {
                if (!is_int($p_category_type) || !in_array($p_category_type, $l_supported_types)) {
                    throw new isys_exception_general('Invalid category type.');
                }
                $l_types = [$p_category_type];
            } else {
                $l_types = $l_supported_types;
            }
            try {
                foreach ($l_types as $l_type) {
                    $p_exportable_categories[$l_type] = self::fetch_exportable_category_data_information($l_type);
                }
            } catch (isys_exception_general $e) {
                throw new isys_exception_general($e->getMessage());
            } // try/catch
            $GLOBALS['g_exportable_categories'] = $p_exportable_categories;
        }
        if (isset($p_match)) {
            if (!is_array($p_match)) {
                throw new isys_exception_general('Invalid matching type.');
            }
            $l_result = [];
            foreach ($p_exportable_categories as $l_type) {
                if (!array_key_exists($l_type, $p_match)) {
                    continue;
                }
                if (!is_array($p_match[$l_type])) {
                    throw new isys_exception_general('Unkown matching format');
                }
                foreach ($l_type as $l_category => $l_value) {
                    if (!array_key_exists($l_category, $p_match[$l_type])) {
                        continue;
                    }
                    $l_result[$l_type][$l_category] = $l_value;
                }
            }

            return $l_result;
        } else {
            return $p_exportable_categories;
        }
    }

    /**
     * Checks whether a category is exportable or not. Alias method of check_exportable_properties().
     *
     * @static
     * @uses    isys_export_cmdb_object::check_exportable_properties
     *
     * @param   array $p_cat_data_information
     *
     * @return  boolean
     */
    public static function isCategoryExportable($p_cat_data_information)
    {
        return self::check_exportable_properties($p_cat_data_information);
    }

    /**
     * Fetches category data information.
     *
     * @global    isys_component_database $g_comp_database
     *
     * @param     integer                 $p_category_type Category type's identifier
     *
     * @return    array
     */
    private static function fetch_exportable_category_data_information($p_category_type)
    {
        global $g_comp_database, $g_dirs;

        $l_result = [];
        $l_dao = new isys_cmdb_dao($g_comp_database);

        // Global categories:
        switch ($p_category_type) {
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                $l_res = $l_dao->get_all_catg();
                $l_class_name = 'isysgui_catg__class_name';
                $l_const = 'isysgui_catg__const';
                break;

            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                $l_res = $l_dao->get_all_cats();
                $l_class_name = 'isysgui_cats__class_name';
                $l_const = 'isysgui_cats__const';
                break;

            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                $l_res = $l_dao->get_all_catg_custom();
                $l_class_name = 'isysgui_catg_custom__class_name';
                $l_const = 'isysgui_catg_custom__const';
                break;

            default:
                throw new isys_exception_general("Unknown category type's identifier.");
                break;
        }

        while ($l_row = $l_res->get_row()) {
            if (!class_exists($l_row[$l_class_name]) || $l_row['isysgui_catg__const'] === 'C__CATG__CUSTOM_FIELDS') {
                // @todo Look at thrown exception(s) and cleanup the database.
                // throw new Exception(sprintf('Unknown category %s.', $l_row[$l_class_name]));
                continue;
            }

            $l_cat = new $l_row[$l_class_name]($g_comp_database);
            $l_cat_data_information = null;

            if (method_exists($l_cat, 'set_catg_custom_id')) {
                $l_cat->set_catg_custom_id($l_row['isysgui_catg_custom__id']);
            }

            // Get properties of category.
            $l_properties = $l_cat->get_properties();
            unset($l_cat);

            if (!is_array($l_properties)) {
                // @todo Look at thrown exception and check if class is still in use or not.
                // If yes update properties else delete all related classes and db entry.
                // Some customers still has some classes which are unused and needs to be deleted
                //file_put_contents(isys_glob_get_temp_dir() . 'misformed_properties.txt', $l_row[$l_class_name].";\n", FILE_APPEND);
                throw new isys_exception_general('Information about category ' . $l_row[$l_const] . ' is invalid. Misformed data.');
                //continue;
            }

            if (count($l_properties) == 0) {
                continue;
            }
            try {
                if ($p_category_type == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                    $l_result[$l_row['isysgui_catg_custom__id']] = self::check_exportable_properties($l_properties);
                } else {
                    if (isset($l_row[$l_const]) && defined($l_row[$l_const])) {
                        $l_result[constant($l_row[$l_const])] = self::check_exportable_properties($l_properties);
                    }
                }
            } catch (isys_exception_general $e) {
                throw new isys_exception_general('Category ' . $l_row[$l_const] . ': ' . $e->getMessage());
            } // try/catch
        }

        unset($l_dao);

        return $l_result;
    }

    /**
     * Checks whether a category is exportable or not.
     *
     * @static
     *
     * @param   array $p_cat_data_information
     *
     * @return  boolean
     */
    private static function check_exportable_properties($p_cat_data_information)
    {
        // If a category has even ONE exportable property, it's exportable.
        foreach ($p_cat_data_information as $l_value) {
            if (isset($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a category to the skipping array.
     *
     * @param  integer $p_category_id
     * @param  integer $p_type
     */
    public function add_skip($p_category_id, $p_type = null)
    {
        if ($p_type === null) {
            $p_type = defined_or_default('C__CMDB__CATEGORY__TYPE_GLOBAL');
        }
        if (is_numeric($p_category_id) && $p_category_id > 0) {
            self::$m_cat_skip[$p_type][$p_category_id] = true;
        }
    }

    /**
     * Removes a category of the skipping array.
     *
     * @param  integer $p_category_id
     * @param  integer $p_type
     */
    public function remove_skip($p_category_id, $p_type = C__CMDB__CATEGORY__TYPE_GLOBAL)
    {
        unset(self::$m_cat_skip[$p_type][$p_category_id]);
    }

    /**
     * Exports object(s).
     *
     * @param mixed $p_object_ids    One or more object identifiers. Can be an array or a comma separated list.
     * @param array $p_categories    Export these categories by their constant.
     * @param int   $p_record_status Status level. Defaults to C__RECORD_STATUS__NORMAL.
     * @param bool  $p_duplicate     Duplicate object. Defaults to false.
     * @param bool  $p_merge         Merge objects. Defaults to false.
     *
     * @return isys_export_cmdb_object
     */
    public function export($p_object_ids, $p_categories = [], $p_record_status = C__RECORD_STATUS__NORMAL, $p_duplicate = false, $p_merge = false, $p_overview_only = false)
    {
        Context::instance()
            ->setContextTechnical(Context::CONTEXT_EXPORT_XML)
            ->setGroup(Context::CONTEXT_GROUP_EXPORT)
            ->setContextCustomer(Context::CONTEXT_EXPORT_XML);

        try {
            // Initialize export array:
            $this->m_export = [];

            // Transform object identifiers into an array:
            $l_object_ids = [];
            if (is_array($p_object_ids)) {
                $l_object_ids = $p_object_ids;
            } elseif (is_numeric($p_object_ids)) {
                $l_object_ids[] = $p_object_ids;
            } elseif (is_string($p_object_ids)) {
                $l_object_ids = explode(',', $p_object_ids);
            }

            // Prepare categories:
            if (!is_array($p_categories) || !count($p_categories)) {
                $p_categories = [
                    C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
                    C__CMDB__CATEGORY__TYPE_SPECIFIC => [],
                    C__CMDB__CATEGORY__TYPE_CUSTOM   => []
                ];
            }

            // Iterate through each object:
            foreach ($l_object_ids as $l_object_id) {
                // Check object identifier:
                if (!is_numeric($l_object_id) || $l_object_id <= 0) {
                    throw new isys_exception_general(sprintf('Object identifier is invalid. (value: %s)', $l_object_id));
                }

                // Export object's categories:
                $this->m_export[$l_object_id] = $this->export_categories($l_object_id, $p_categories, $p_record_status, $p_duplicate, $p_merge, $p_overview_only);

                if ($p_merge) {
                    $this->m_export = [
                        10 => $this->merge_exports($this->m_export)
                    ];
                }
            }
        } catch (Exception $e) {
            isys_notify::warning('Error: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Export object category's properties.
     *
     * @param   array   $p_object_properties
     * @param   array   $p_row
     * @param   integer $p_cattype
     * @param   boolean $p_disable_provides If this is set to "true" the provide-attribute will be ignored (provide export).
     *
     * @throws  isys_exception_cmdb
     * @return  array
     */
    public function export_properties($p_object_properties, $p_row, $p_cattype, $p_disable_provides = false)
    {
        $l_data = [];

        // Prepare properties.
        foreach ($p_object_properties as $l_tag => $l_property) {
            $l_new = [];

            if (!isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) || ($p_disable_provides === false && $l_property[C__PROPERTY__PROVIDES__EXPORT] === false)) {
                continue;
            }

            if (!isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) || empty($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK])) {
                // No helper class.
                if ($p_cattype != C__CMDB__CATEGORY__TYPE_CUSTOM) {
                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                        $l_new['value'] = $p_row[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                    } else {
                        $l_new['value'] = $p_row[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                    }
                } else {
                    if ($l_tag === 'description') {
                        $l_new['value'] = $p_row['commentary_' . $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]];
                    } else {
                        $l_new['value'] = $p_row[$l_tag];
                    }
                }
            } elseif (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK])) {
                // Check if helper class exists.
                if (class_exists($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                    // Create new instance of the helper class:
                    $l_helper = new $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                        $p_row,
                        $this->m_database,
                        $l_property[C__PROPERTY__DATA],
                        $l_property[C__PROPERTY__FORMAT],
                        $l_property[C__PROPERTY__UI]
                    );

                    if (!empty($p_object_properties[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                        $l_unit_key = $p_object_properties[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];

                        if (method_exists($l_helper, 'set_unit_const')) {
                            if (isset($p_object_properties[$l_unit_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                                $l_const = $p_row[$p_object_properties[$l_unit_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                            } else {
                                $l_const = $p_row[$p_object_properties[$l_unit_key][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__const'];
                            }

                            $l_helper->set_unit_const($l_const);
                        }
                    }

                    // Call the helper's method:
                    if (method_exists($l_helper, $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                        if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]) &&
                            array_key_exists($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS], $p_row)) {
                            $l_callbackValue = $p_row[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                        } elseif ($p_cattype == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                            // @See ID-5320 If key does not exist in $p_row than the entry does not exist
                            $l_callbackValue = null;
                        } else {
                            $l_callbackValue = $p_row[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                        }

                        $l_new['value'] = call_user_func([
                            $l_helper,
                            $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                        ], $l_callbackValue);
                    } else {
                        throw new isys_exception_cmdb(sprintf(
                            'Method %s in helper class %s does not exist.',
                            $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1],
                            $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                        ));
                    }

                    unset($l_helper);
                }
            }

            // Add title:
            $l_new['title'] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];

            // Add tag:
            $l_new['tag'] = $l_tag;
            $l_data[] = $l_new;
        }

        return $l_data;
    }

    /**
     * Parses output data with the export formatter.
     *
     * @param   array   $p_data       (optional) Output data. Defaults to null.
     * @param   string  $p_stylesheet (optional) Path to stylesheet file. Defaults to null.
     * @param   boolean $p_translate
     *
     * @return  isys_export_type  Formated data
     */
    public function parse($p_data = null, $p_stylesheet = null, $p_translate = false)
    {
        if (!is_null($p_stylesheet) && method_exists($this->m_export_formatter, 'set_stylesheet')) {
            $this->m_export_formatter->set_stylesheet($p_stylesheet);
        }

        if (is_array($p_data)) {
            return $this->m_export_formatter->parse($p_data, null, $p_translate);
        } elseif (is_array($this->m_export)) {
            return $this->m_export_formatter->parse($this->m_export, null, $p_translate);
        } else {
            throw new isys_exception_cmdb('Wrong input format. Data must be an array.');
        }
    }

    /**
     * @param   array $p_exports
     *
     * @return  array
     */
    private function merge_exports($p_exports)
    {
        $l_result = [];

        $l_result['head'] = [
            'id'          => '%ID%',
            'title'       => '%TITLE%',
            'sysid'       => '%SYSID%',
            'created'     => '%CREATED%',
            'created_by'  => '%CREATEDBY%',
            'updated'     => '%UPDATED%',
            'updated_by'  => '%UPDATEDBY%',
            'status'      => '%STATUS%',
            'cmdb_status' => '%CMDB_STATUS%',
            'description' => '%DESCRIPTION%',
            'type'        => [
                'id'           => '%OBJTYPEID%',
                'const'        => '%OBJTYPECONST%',
                'title'        => "%OBJTYPETITLE%",
                'title_lang'   => '%OBJTYPELANG%',
                'group'        => '%OBJTYPEGROUP%',
                'sysid_prefix' => '%OBJTYPESYSIDPREFIX%',
            ]
        ];

        if (is_array($p_exports)) {
            foreach ($p_exports as $l_object) {
                if (is_array($l_object)) {
                    foreach ($l_object as $l_cattype => $l_categories) {
                        if (is_numeric($l_cattype)) {
                            foreach ($l_categories as $l_catid => $l_catdata) {
                                foreach ($l_catdata as $l_catentryID => $l_catentry) {
                                    if (is_numeric($l_catentryID)) {
                                        if ($l_catid === defined_or_default('C__CATG__GLOBAL')) {
                                            foreach ($l_catentry as $l_key => $l_property) {
                                                if ($l_property[C__DATA__TAG] == 'cmdb_status' && $this->overwriteCmdbStatusOnMassChange === true) {
                                                    continue;
                                                }

                                                if (isset($l_result['head'][$l_property[C__DATA__TAG]])) {
                                                    $l_catentry[$l_key][C__DATA__VALUE] = $l_result['head'][$l_property[C__DATA__TAG]];
                                                }
                                            }
                                        }

                                        $l_result[$l_cattype][$l_catid][$l_catentryID] = $l_catentry;
                                    } else {
                                        $l_result[$l_cattype][$l_catid]['head'] = $l_catentry;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $l_result;
    }

    /**
     * Exports object's categories.
     *
     * @param   integer $p_object_id
     * @param   array   $p_categories
     * @param   integer $p_record_status @todo Never used. Remove!
     * @param   boolean $p_duplicate     (optional) Duplicate object? Defaults to false.
     * @param   boolean $p_merge
     *
     * @throws  isys_exception_cmdb
     * @return  array
     */
    private function export_categories($p_object_id, $p_categories, $p_record_status = C__RECORD_STATUS__NORMAL, $p_duplicate = false, $p_merge = false, $p_overview_only = false)
    {
        // Initialize returning result:
        $l_output = [];

        // Prepare categories:

        if (is_array($p_categories[0]) || is_array($p_categories[1]) || is_array($p_categories[2]) || is_array($p_categories[3]) || is_array($p_categories[4])) {
            foreach ($p_categories as $l_key => $l_val) {
                if (is_array($l_val) && count($l_val) > 0) {
                    $p_categories[$l_key] = array_flip($l_val);
                }
            }
        } else {
            $l_tmp = array_flip($p_categories);
            $p_categories = $l_tmp;
        }

        // Use overview category to retrieve all categories for an object:
        $l_cat = new isys_cmdb_dao_category_g_overview($this->m_database);
        if (!$l_cat->obj_exists($p_object_id)) {
            return false;
        }

        // Get object type identifier:
        $l_otdata = $l_cat->get_type_group_by_object_id($p_object_id);
        $l_otrow = $l_otdata->get_row();
        $l_object_type = $l_otrow['isys_obj_type__id'];
        // Duplication: create a system-wide unique identifier:
        if ($p_duplicate) {
            $l_sysid_prefix = (!empty($l_otrow['isys_obj_type__sysid_prefix'])) ? $l_otrow['isys_obj_type__sysid_prefix'] : C__CMDB__SYSID__PREFIX;
            // @see  ID-6471  Increment, if we use a selfdefined index.
            $l_sysid_suffix = (($l_sysid_prefix == C__CMDB__SYSID__PREFIX) ? time() : ($l_cat->get_last_obj_id_from_type() + 1)) + self::$m_duplicate_counter++;
            $l_otrow['isys_obj__sysid'] = $l_sysid_prefix . $l_sysid_suffix;

            if (strlen($l_otrow['isys_obj__sysid']) < 13) {
                $l_zeros = '';
                for ($i = 0;$i < (13 - strlen($l_otrow['isys_obj__sysid']));$i++) {
                    $l_zeros .= '0';
                }
                $l_sysid_suffix = $l_zeros . $l_sysid_suffix;
                $l_otrow['isys_obj__sysid'] = $l_sysid_prefix . $l_sysid_suffix;
            }
        }
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        // Collect object information:
        $l_output['head'] = [
            'id'          => $p_object_id,
            'title'       => $l_otrow['isys_obj__title'],
            'sysid'       => $l_otrow['isys_obj__sysid'],
            'status'      => $l_otrow['isys_obj__status'],
            'created'     => $l_otrow['isys_obj__created'] ? $l_otrow['isys_obj__created'] : $l_empty_value,
            'created_by'  => $l_otrow['isys_obj__created_by'] ? $l_otrow['isys_obj__created_by'] : $l_empty_value,
            'updated'     => $l_otrow['isys_obj__updated'] ? $l_otrow['isys_obj__updated'] : $l_empty_value,
            'updated_by'  => $l_otrow['isys_obj__updated_by'] ? $l_otrow['isys_obj__updated_by'] : $l_empty_value,
            'cmdb_status' => $l_otrow['isys_obj__isys_cmdb_status__id'],
            'description' => $l_otrow['isys_obj__description'],
            'type'        => [
                'id'           => $l_object_type,
                'const'        => $l_otrow['isys_obj_type__const'],
                'title'        => isys_application::instance()->container->get('language')
                    ->get($l_otrow['isys_obj_type__title']),
                'title_lang'   => $l_otrow['isys_obj_type__title'],
                'group'        => $l_otrow['isys_obj_type_group__const'],
                'sysid_prefix' => $l_otrow['isys_obj_type__sysid_prefix']
            ]
        ];

        // Iterate through each category type:
        $l_category_data_information = null;
        $l_has_operating_system = false;

        foreach ([
                     C__CMDB__CATEGORY__TYPE_GLOBAL,
                     C__CMDB__CATEGORY__TYPE_SPECIFIC,
                     C__CMDB__CATEGORY__TYPE_CUSTOM
                 ] as $l_cattype) {

            // Get all availlable categories:
            try {
                if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                    $l_all_categories = $l_cat->get_categories_as_array($l_object_type, $p_object_id, $l_cattype, C__RECORD_STATUS__NORMAL, $p_overview_only);

                    // Also take care of durable global categories:
                    $l_durables = $l_cat->get_durable_catg()
                        ->__as_array();
                    foreach ($l_durables as $l_durable) {
                        if (class_exists($l_durable['isysgui_catg__class_name'])) {
                            $l_all_categories[$l_durable['isysgui_catg__id']] = [
                                'const'        => $l_durable['isysgui_catg__const'],
                                'title'        => isys_application::instance()->container->get('language')
                                    ->get($l_durable['isysgui_catg__title']),
                                'dao'          => new $l_durable['isysgui_catg__class_name']($this->m_database),
                                'source_table' => $l_durable['isysgui_catg__source_table']
                            ];
                        }
                    }

                    $this->m_all_categories[$l_cattype] = $l_all_categories;
                } elseif ($l_cattype == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                    $l_all_categories = $l_cat->get_custom_categories_as_array($l_object_type, $p_object_id, $l_cattype, C__RECORD_STATUS__NORMAL, false, false);
                } else {
                    $l_all_categories = $l_cat->get_categories_as_array($l_object_type, $p_object_id, $l_cattype, C__RECORD_STATUS__NORMAL, false, true);

                    $this->m_all_categories[$l_cattype] = $l_all_categories;
                }
            } catch (Exception $e) {
                throw new isys_exception_cmdb($e->getMessage());
            }

            // Iterate through these categories:
            if (is_array($l_all_categories) && count($l_all_categories)) {
                foreach ($l_all_categories as $l_category_id => $l_data) {
                    // Skip defined categories (e. g. overview category with no content):
                    if (isset(self::$m_cat_skip[$l_cattype][$l_category_id])) {
                        continue;
                    }

                    if (is_countable($p_categories[$l_cattype]) && count($p_categories[$l_cattype]) !== 0) {
                        // Skip if this category is not wanted (by parameter $p_categories) */
                        if (isset($p_categories[$l_cattype]) && !isset($p_categories[$l_cattype][$l_category_id])) {
                            continue;
                        }

                        if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL && $p_categories && !isset($p_categories[$l_cattype][$l_category_id])) {
                            continue;
                        }
                    } else {
                        // Skip category
                        continue;
                    }

                    // Get category's DAO instance:
                    $l_category_dao = $l_data['dao'];

                    // Retrieve all data for category and object.
                    if (!is_object($l_category_dao) || !method_exists($l_category_dao, 'get_data')) {
                        continue;
                    }

                    // Write head info about category:
                    $l_output[$l_cattype][$l_category_id]['head'] = [
                        'id'            => $l_category_id,
                        'title'         => $l_data['title'],
                        'const'         => $l_data['const'],
                        'table'         => $l_data['source_table'],
                        'category_type' => $l_cattype,
                        'multivalued'   => $l_data['multivalued']
                    ];

                    // For custom categories:
                    if (method_exists($l_category_dao, 'set_catg_custom_id')) {
                        $l_category_dao->set_catg_custom_id($l_category_id);
                        $l_category_dao->unset_properties();
                        $l_category_dao->set_config($l_category_id);
                        $l_output[$l_cattype][$l_category_id]['head']['multivalued'] = $l_category_dao->is_multivalued();
                    }

                    $l_condition = '';
                    if (method_exists($l_category_dao, 'get_export_condition')) {
                        $l_condition = $l_category_dao->get_export_condition();
                    }

                    $l_status = C__RECORD_STATUS__NORMAL;

                    // Check whether printview mode is active
                    if (Context::instance()->getContextCustomer() === Context::CONTEXT_EXPORT_PRINTVIEW && $l_category_dao instanceof Printable) {
                        $l_dataresult = $l_category_dao->getDataForPrintView(null, $p_object_id, $l_condition, null, $l_status);
                    } else {
                        // Get category data.
                        $l_dataresult = $l_category_dao->get_data(null, $p_object_id, $l_condition, null, $l_status);
                    }

                    // Set source table
                    $l_table = $l_category_dao->get_table();

                    // No data available:
                    if ($l_dataresult->num_rows() <= 0) {
                        continue;
                    }

                    $l_category_data_information = $l_category_dao->get_properties();

                    if ($l_cattype == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                        $report = $l_category_dao->getReport($p_object_id);

                        if ($report !== false) {
                            if ($report !== null) {
                                $l_output[$l_cattype][$l_category_id]['head']['title'] = $report['isys_report__title'];

                                while ($l_row = $l_dataresult->get_row()) {
                                    $id = $l_row['__id__'];
                                    unset($l_row['__id__']);

                                    foreach ($l_row as $key => $value) {
                                        $l_output[$l_cattype][$l_category_id][$id][] = [
                                            'title' => $key,
                                            'value' => $value,
                                            'tag'   => strtolower($key)
                                        ];
                                    }
                                }
                            }
                        } else {
                            $l_data_id = null;
                            $l_merged_arr = [];

                            // Iterate through custom fields:
                            while ($l_row = $l_dataresult->get_row()) {
                                $l_key = $l_row['isys_catg_custom_fields_list__field_type'] . '_' . $l_row['isys_catg_custom_fields_list__field_key'];
                                $l_value = $l_row['isys_catg_custom_fields_list__field_content'];

                                if (!!($l_category_data_information[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][C__PROPERTY__UI__TYPE__MULTISELECT]) ||
                                    !!($l_category_data_information[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION])) {
                                    $l_merged_arr[$l_row['isys_catg_custom_fields_list__data__id']][$l_key][] = $l_value;
                                } else {
                                    $l_merged_arr[$l_row['isys_catg_custom_fields_list__data__id']][$l_key] = $l_value;
                                }

                                $l_merged_arr[$l_row['isys_catg_custom_fields_list__data__id']] = array_merge(
                                    $l_merged_arr[$l_row['isys_catg_custom_fields_list__data__id']],
                                    $l_row
                                );
                            } // while custom fields

                            foreach ($l_merged_arr as $l_data_key_id => $l_merge_test) {
                                $l_prop_export = $this->export_properties($l_category_data_information, $l_merge_test, $l_cattype);
                                $l_output[$l_cattype][$l_category_id][$l_data_key_id] = $l_prop_export;
                            }
                        }
                    } else {
                        if (empty($l_table)) {
                            $l_table = (!strpos($l_data['source_table'], '_list') && !strpos($l_data['source_table'], '_2_')) ? $l_data['source_table'] .
                                "_list" : $l_data['source_table'];
                        }

                        // Iterate through category's data:
                        while ($l_row = $l_dataresult->get_row()) {
                            $l_data_id = $l_row[$l_table . '__id'];

                            // @todo isys_netp_ifacel is not conform to the i-doit developer's conventions. It should be called isys_catg_interface_l.
                            if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                                if (is_value_in_constants($l_category_id, [
                                    'C__CATG__NETWORK_LOG_PORT',
                                    'C__CMDB__SUBCAT__NETWORK_INTERFACE_L'
                                ])) {
                                    $l_data_id = $l_row['isys_catg_log_port_list__id'];
                                } elseif ($l_category_id == defined_or_default('C__CATG__OPERATING_SYSTEM')) {
                                    $l_has_operating_system = true;
                                }
                            }

                            $l_prop_export = $this->export_properties($l_category_data_information, $l_row, $l_cattype);

                            $l_output[$l_cattype][$l_category_id][$l_data_id] = $l_prop_export;
                        }
                    }
                }
            }
        }

        if ($l_has_operating_system && defined('C__CATG__OPERATING_SYSTEM')) {
            // Remove operating system from software assignment otherwise the entry will also be created in mass-template
            $l_os_data = $l_output[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__OPERATING_SYSTEM')];
            unset($l_os_data['head']);
            $l_data_id = key($l_os_data);
            if (defined('C__CATG__APPLICATION') && isset($l_output[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')][$l_data_id])) {
                unset($l_output[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')][$l_data_id]);
            }
        }


        return $l_output;
    }

    /**
     * Constructor
     *
     * @param  string                  $p_export_type (optional) Export type. Defaults to 'isys_export_type_xml'.
     * @param  isys_component_database $p_database    (optional) Database connection. Defaults to null.
     */
    public function __construct($p_export_type = 'isys_export_type_xml', &$p_database = null)
    {
        self::initSkippedCategories();
        parent::__construct($p_export_type, $p_database);
    }

    /**
     * Check whether category is blacklisted or not
     *
     * @param int   $p_category_type    [C__CMDB__CATEGORY__TYPE_GLOBAL, C__CMDB__CATEGORY__TYPE_SPECIFIC, C__CMDB__CATEGORY__TYPE_CUSTOM]
     * @param int   $p_category_id
     *
     * @return bool
     */
    public static function isCategoryBlacklisted($p_category_type, $p_category_id)
    {
        try {
            self::initSkippedCategories();
            // Try getting id out of category skip register
            return self::$m_cat_skip[$p_category_type][$p_category_id];
        } catch (Exception $e) {
            return false;
        }
    }
}

global $g_exportable_categories;
