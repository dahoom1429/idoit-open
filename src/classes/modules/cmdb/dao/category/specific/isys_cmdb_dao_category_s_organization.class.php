<?php
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Property;

/**
 * i-doit
 *
 * DAO: specific category for organizations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_organization extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'organization';

    /**
     * Category's identifier
     *
     * @var int
     *
     * @fixme No standard behavior!
     */
    protected $m_category_id;

    /**
     * Category's ui class name.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_ui = 'isys_cmdb_ui_category_s_organization_master';

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_organization_list " .

            "INNER JOIN isys_obj " . "ON " . "isys_cats_organization_list__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_connection " .
            "ON isys_cats_organization_list__isys_connection__id = isys_connection__id " .

            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_organization_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_organization_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__title'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_TITLE'
                ]
            ]),
            'telephone'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Telephone'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__telephone'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_PHONE'
                ]
            ]),
            'fax'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_FAX',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fax'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__fax'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_FAX'
                ]
            ]),
            'website'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_WEBSITE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Website'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__website'
                    // @todo add C__PROPERTY__DATA__SELECT for lists and build a link
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__ORGANISATION_WEBSITE',
                    C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__LINK,
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTarget' => '_blank',
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'headquarter' => (new DialogProperty(
                'C__CONTACT__ORGANISATION_ASSIGNMENT',
                'LC__CONTACT__ORGANISATION_ASSIGNMENT',
                'isys_cats_organization_list__isys_connection__id',
                'isys_cats_organization_list',
                'isys_connection',
                true,
                [
                    'isys_export_helper',
                    'connection'
                ]
            ))->mergePropertyData(
                [
                    Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_organization_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_organization_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_cats_organization_list',
                        'isys_cats_organization_list__id',
                        'isys_cats_organization_list__isys_obj__id'
                    ),
                    Property::C__PROPERTY__DATA__JOIN => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_organization_list',
                            'LEFT',
                            'isys_cats_organization_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_cats_organization_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ],
                    Property::C__PROPERTY__DATA__RELATION_TYPE => defined_or_default('C__RELATION_TYPE__ORGANIZATION_HEADQUARTER'),
                    Property::C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                        [
                            'isys_cmdb_dao_category_s_organization_master',
                            'callback_property_relation_handler'
                        ],
                        [
                            'isys_cmdb_dao_category_s_organization_master',
                            true
                        ]
                    )
                ]
            )->setPropertyUiOffset(
                Property::C__PROPERTY__UI__PARAMS,
                [
                    'p_arData' => new isys_callback([
                        'isys_cmdb_dao_category_s_organization_master',
                        'callback_property_headquarter_selection'
                    ]),
                    'chosen'   => true
                ]
            )->setPropertyProvidesOffset(
                Property::C__PROPERTY__PROVIDES__REPORT,
                false
            ),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__ORGANIZATION', 'C__CATS__ORGANIZATION')
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
                $p_category_data['data_id'] = $this->create_connector('isys_cats_organization_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['telephone'][C__DATA__VALUE],
                    $p_category_data['properties']['fax'][C__DATA__VALUE],
                    $p_category_data['properties']['website'][C__DATA__VALUE],
                    $p_category_data['properties']['headquarter'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Dynamic property handling for getting the formatted website attribute.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_website($p_row)
    {
        global $g_comp_database;

        $l_organization_website = isys_cmdb_dao_category_s_organization_master::instance($g_comp_database)
            ->get_data(null, $p_row['isys_obj__id'])
            ->get_row_value('isys_cats_organization_list__website');

        if (empty($l_organization_website)) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return isys_factory::get_instance('isys_smarty_plugin_f_link')
            ->navigation_view(isys_application::instance()->template, [
                'name'              => 'dynamic-property-organization-website',
                'p_strValue'        => $l_organization_website,
                'p_strTarget'       => '_blank',
                'p_bInfoIconSpacer' => 0
            ]);
    }

    /**
     * Save specific category monitor
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return;

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_organization_list__status"];

        $l_list_id = $l_catdata["isys_cats_organization_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_organization_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        if ($l_list_id) {
            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CONTACT__ORGANISATION_TITLE"],
                $_POST["C__CONTACT__ORGANISATION_STREET"],
                $_POST["C__CONTACT__ORGANISATION_POSTAL_CODE"],
                $_POST["C__CONTACT__ORGANISATION_CITY"],
                $_POST["C__CONTACT__ORGANISATION_COUNTRY"],
                $_POST["C__CONTACT__ORGANISATION_PHONE"],
                $_POST["C__CONTACT__ORGANISATION_FAX"],
                $_POST["C__CONTACT__ORGANISATION_WEBSITE"],
                $_POST["C__CONTACT__ORGANISATION_ASSIGNMENT"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_bRet) {
                if (!$this->update_orga_object($_GET[C__CMDB__GET__OBJECT], $_POST["C__CONTACT__ORGANISATION_TITLE"])) {
                    throw new isys_exception_dao_cmdb("Error while updating organization object");
                }
            }
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    public function save(
        $p_catlevel,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_title = null,
        $p_street = null,
        $p_zip_code = null,
        $p_city = null,
        $p_country = null,
        $p_telephone = null,
        $p_fax = null,
        $p_website = null,
        $p_headquarter = null,
        $p_description = null
    ) {
        return;
        $l_dao = new isys_cmdb_dao_connection($this->get_database_component());
        $l_old_data = $this->get_data($p_catlevel)
            ->__to_array();

        if (!empty($l_old_data["isys_cats_organization_list__isys_connection__id"])) {
            $l_id = $l_dao->update_connection($l_old_data["isys_cats_organization_list__isys_connection__id"], $p_headquarter);
        } else {
            $l_id = $l_dao->add_connection($p_headquarter);
        }

        $l_sql = "UPDATE isys_cats_organization_list " . "INNER JOIN isys_obj ON isys_obj__id = isys_cats_organization_list__isys_obj__id " . "SET " . "isys_obj__status = " .
            $this->convert_sql_id($p_status) . ", " . "isys_obj__title = " . $this->convert_sql_text($p_title) . ", " . "isys_cats_organization_list__title= " .
            $this->convert_sql_text($p_title) . ", " . "isys_cats_organization_list__status = " . $this->convert_sql_id($p_status) . ", " .
            "isys_cats_organization_list__street = " . $this->convert_sql_text($p_street) . ", " . "isys_cats_organization_list__zip_code = " .
            $this->convert_sql_text($p_zip_code) . ", " . "isys_cats_organization_list__city = " . $this->convert_sql_text($p_city) . ", " .
            "isys_cats_organization_list__country = " . $this->convert_sql_text($p_country) . ", " . "isys_cats_organization_list__telephone = " .
            $this->convert_sql_text($p_telephone) . ", " . "isys_cats_organization_list__fax =" . $this->convert_sql_text($p_fax) . ", " .
            "isys_cats_organization_list__website =" . $this->convert_sql_text($p_website) . ", " . "isys_cats_organization_list__isys_connection__id =" .
            $this->convert_sql_id($l_id) . ", " . "isys_cats_organization_list__description =" . $this->convert_sql_text($p_description);

        $l_sql .= " WHERE isys_cats_organization_list__id = " . $this->convert_sql_id($p_catlevel);

        if ($this->update($l_sql)) {
            return $this->apply_update();
        }

        return false;
    }

    public function update_orga_object($p_object_id, $p_title)
    {
        $l_sql = "UPDATE isys_obj SET isys_obj__title = " . $this->convert_sql_text($p_title) . " WHERE isys_obj__id = " . $this->convert_sql_id($p_object_id);

        $this->update($l_sql);

        if ($this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save global category monitor element
     *
     * @param $p_cat_level level to save, default 0
     * @param &$p_new_id   returns the __id of the new record
     */
    public function attachObjects(array $p_post)
    {
        return null;
    }

    /**
     * Executes the query to create new oragization
     *
     * @return int the newly created ID or false
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_telephone, $p_fax, $p_website, $p_connection_id, $p_headquarter_id, $p_description)
    {
        $l_sql = "INSERT INTO isys_cats_organization_list (
					isys_cats_organization_list__isys_obj__id,
					isys_cats_organization_list__title,
					isys_cats_organization_list__status,
					isys_cats_organization_list__telephone,
					isys_cats_organization_list__fax,
					isys_cats_organization_list__website,
					isys_cats_organization_list__isys_connection__id,
					isys_cats_organization_list__headquarter,
					isys_cats_organization_list__description)
					VALUES ";

        $l_sql .= "(" . $this->convert_sql_id($p_objID) . ",
					" . $this->convert_sql_text($p_title) . ",
					" . $this->convert_sql_id($p_newRecStatus) . ",
					" . $this->convert_sql_text($p_telephone) . ",
					" . $this->convert_sql_text($p_fax) . ",
					" . $this->convert_sql_text($p_website) . ",
					" . $this->convert_sql_id($p_connection_id) . ",
					" . $this->convert_sql_id($p_headquarter_id) . ",
					" . $this->convert_sql_text($p_description) . "
					)";

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    public function __construct(isys_component_database $p_db)
    {
        $this->m_category_id = defined_or_default('C__CATS__ORGANIZATION');
        parent::__construct($p_db);
    }
}
