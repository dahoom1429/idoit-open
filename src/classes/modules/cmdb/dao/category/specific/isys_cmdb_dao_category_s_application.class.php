<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;

define('C__APPLICATION__INSTALLATION_TYPE__AUTOMATIC', 0);
define('C__APPLICATION__INSTALLATION_TYPE__MANUAL', 1);

/**
 * i-doit
 *
 * DAO: specific category for applications.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_application extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'application';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for the installation type dialog field.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_installation_types()
    {
        return [
            C__APPLICATION__INSTALLATION_TYPE__AUTOMATIC => isys_application::instance()->container->get('language')
                ->get("LC__CMDB__CATS__APPLICATION_INSTALLATION_TYPE__AUTOMATIC"),
            C__APPLICATION__INSTALLATION_TYPE__MANUAL    => isys_application::instance()->container->get('language')
                ->get("LC__CMDB__CATS__APPLICATION_INSTALLATION_TYPE__MANUAL")
        ];
    }

    /**
     * Dynamic property handling for getting the formatted CPU data.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_installation_count($p_row)
    {
        $objId = ($p_row['isys_cats_application_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return isys_cmdb_dao_category_s_application_assigned_obj::instance(isys_application::instance()->database)
            ->get_count($objId);
    }

    /**
     * Callback method for the installation type dialog field
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_installation_type(isys_request $p_request)
    {
        return isys_cmdb_dao_category_s_application::get_installation_types();
    }

    /**
     * Save specific category application.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();
        $p_intOldRecStatus = $l_catdata["isys_cats_application_list__status"];

        $l_id = $l_catdata['isys_cats_application_list__id'];

        if (empty($l_id)) {
            $l_id = $this->create_connector("isys_cats_application_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        if ($l_id) {
            $l_bRet = $this->save(
                $l_id,
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATS__APPLICATION_SPECIFICATION'],
                $_POST['C__CATS__APPLICATION_MANUFACTURER_ID'],
                $_POST['C__CATS__APPLICATION_RELEASE'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATS__APPLICATION_INSTALLATION_TYPE'],
                $_POST['C__CATS__APPLICATION_REGISTRATION_KEY'],
                $_POST['C__CATS__APPLICATION_INSTALL_PATH']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? $l_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   string  $p_specification
     * @param   integer $p_manufacturerID
     * @param   string  $p_release
     * @param   string  $p_description
     * @param   integer $p_installation_type
     * @param   string  $p_registration_key
     * @param   string  $p_install_path
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_specification,
        $p_manufacturerID,
        $p_release,
        $p_description,
        $p_installation_type = null,
        $p_registration_key = null,
        $p_install_path = null
    ) {
        $l_strSql = "UPDATE isys_cats_application_list
			SET isys_cats_application_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_application_list__specification  = " . $this->convert_sql_text($p_specification) . ",
			isys_cats_application_list__release  = " . $this->convert_sql_text($p_release) . ",
			isys_cats_application_list__isys_application_manufacturer__id  = " . $this->convert_sql_id($p_manufacturerID) . ",
			isys_cats_application_list__isys_installation_type__id = " . $this->convert_sql_id($p_installation_type) . ",
			isys_cats_application_list__registration_key = " . $this->convert_sql_text($p_registration_key) . ",
			isys_cats_application_list__install_path = " . $this->convert_sql_text($p_install_path) . ",
			isys_cats_application_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_cats_application_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_specification
     * @param   integer $p_manufacturerID
     * @param   string  $p_release
     * @param   string  $p_description
     * @param   integer $p_installation_type
     * @param   string  $p_registration_key
     * @param   string  $p_install_path
     *
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_newRecStatus,
        $p_specification,
        $p_manufacturerID,
        $p_release,
        $p_description,
        $p_installation_type = null,
        $p_registration_key = null,
        $p_install_path = null
    ) {
        $l_strSql = "INSERT IGNORE INTO isys_cats_application_list
			SET isys_cats_application_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_application_list__specification  = " . $this->convert_sql_text($p_specification) . ",
			isys_cats_application_list__release  = " . $this->convert_sql_text($p_release) . ",
			isys_cats_application_list__isys_application_manufacturer__id  = " . $this->convert_sql_id($p_manufacturerID) . ",
			isys_cats_application_list__isys_obj__id  = " . $this->convert_sql_id($p_objID) . ",
			isys_cats_application_list__isys_installation_type__id = " . $this->convert_sql_id($p_installation_type) . ",
			isys_cats_application_list__registration_key = " . $this->convert_sql_text($p_registration_key) . ",
			isys_cats_application_list__install_path = " . $this->convert_sql_text($p_install_path) . ",
			isys_cats_application_list__status = " . $this->convert_sql_id($p_newRecStatus) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Builds an array with minimal requirements for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_application_manufacturer', $p_data['manufacturer']);
        } else {
            $l_manufacturer = isys_import_handler::check_dialog('isys_application_manufacturer', 'LC__UNIVERSAL__NOT_SPECIFIED');
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'specification'     => [
                    'value' => $p_data['specification']
                ],
                'manufacturer'      => [
                    'value' => $l_manufacturer
                ],
                'release'           => [
                    'value' => $p_data['release']
                ],
                'installation_path' => [
                    'value' => $p_data['install_path']
                ],
                'description'       => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_installation_count' => new DynamicProperty(
                'LC__CMDB__CATS__APPLICATION_INSTALLATION_COUNT',
                'isys_cats_application_list__isys_obj__id',
                'isys_cats_application_list',
                [
                    $this,
                    'dynamic_property_callback_installation_count'
                ]
            ),
        ];
    }

    /**
     * Returns how many entries exists. The folder only needs to know if there are any entries in its subcategories.
     *
     * @param null $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == defined_or_default('C__CATS__APPLICATION')) {
            $l_sql = 'SELECT
				(
				IFNULL((SELECT isys_cats_application_list__id AS cnt FROM isys_cats_application_list
					WHERE isys_cats_application_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_cats_app_variant_list__id AS cnt FROM  isys_cats_app_variant_list
					WHERE  isys_cats_app_variant_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_catg_application_list__id AS cnt FROM isys_catg_application_list
					INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
					WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				)
				AS cnt';

            return ($this->retrieve($l_sql)
                    ->get_row_value('cnt') > 0) ? 1 : 0;
        } else {
            return parent::get_count($p_obj_id);
        }
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_application_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_application_list__isys_obj__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_cats_application_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_cats_application_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    protected function properties()
    {
        return [
            'specification'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SERVICE_SPECIFICATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Specification'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_application_list__specification'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__APPLICATION_SPECIFICATION'
                ]
            ]),
            'manufacturer' => (new DialogPlusProperty(
                'C__CATS__APPLICATION_MANUFACTURER_ID',
                'LC__CMDB__CATG__MANUFACTURE',
                'isys_cats_application_list__isys_application_manufacturer__id',
                'isys_cats_application_list',
                'isys_application_manufacturer'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'installation' => new DialogProperty(
                'C__CATS__APPLICATION_INSTALLATION_TYPE',
                'LC__CMDB__CATS__APPLICATION_INSTALLATION_TYPE',
                'isys_cats_application_list__isys_installation_type__id',
                'isys_cats_application_list',
                'isys_installation_type'
            ),
            'registration_key'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__REGISTRATION_KEY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Registration key'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_application_list__registration_key'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__APPLICATION_REGISTRATION_KEY'
                ]
            ]),
            'install_path'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__APPLICATION__INSTALL_PATH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Install path'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_application_list__install_path'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__APPLICATION_INSTALL_PATH'
                ]
            ]),
            'installation_count' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__APPLICATION_INSTALLATION_COUNT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Amount of installations'
                ],
                C__PROPERTY__DATA     => [

                        C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                            '
                            SELECT COUNT(isys_catg_application_list__id)
                            FROM isys_connection
                            INNER JOIN isys_catg_application_list ON isys_catg_application_list__isys_connection__id = isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_application_list__isys_obj__id',
                            'isys_connection',
                            'isys_connection__id',
                            'isys_connection__isys_obj__id',
                            '',
                            '',
                            idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                                'isys_catg_application_list__status = ' . C__RECORD_STATUS__NORMAL,
                                'AND isys_obj__status = ' . C__RECORD_STATUS__NORMAL
                            ])
                        )
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => true,
                        C__PROPERTY__PROVIDES__VIRTUAL    => true,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false
                    ]
                ]),
            'description'        => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_application_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__APPLICATION', 'C__CATS__APPLICATION')
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
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_application_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['specification'][C__DATA__VALUE],
                    $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                    $p_category_data['properties']['release'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['installation'][C__DATA__VALUE],
                    $p_category_data['properties']['registration_key'][C__DATA__VALUE],
                    $p_category_data['properties']['install_path'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
