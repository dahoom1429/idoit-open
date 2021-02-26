<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: Specific category for persons in organizations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_organization_person extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'organization_person';

    /**
     * Category's constant.
     *
     * @todo  No standard behavior!
     * @var   string
     */
    protected $m_category_const = 'C__CATS__ORGANIZATION_PERSONS';

    /**
     * Category's identifier.
     *
     * @todo  No standard behavior!
     * @var   integer
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATS__ORGANIZATION_PERSONS;

    /**
     * Name of property which should be used as identifier.
     *
     * @var string
     */
    protected $m_entry_identifier = 'object';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Flag which defines if the category is only a list with an object browser.
     *
     * @var  boolean
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser.
     *
     * @var  string
     */
    protected $m_object_browser_property = 'object';

    /**
     * The category's table.
     *
     * @todo  No standard behavior!
     * @var   string
     */
    protected $m_table = 'isys_cats_person_list';

    /**
     * Callback function for dataretrieval for UI.
     *
     * @param   isys_request $p_request
     *
     * @return  isys_component_dao_result
     */
    public function callback_property_object(isys_request $p_request)
    {
        return $this->get_data(null, $p_request->get_object_id());
    }

    /**
     * Get count for graying the category title.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = 'SELECT COUNT(isys_cats_person_list__id) AS `count` FROM isys_obj
			LEFT JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
			INNER JOIN isys_cats_organization_list ON isys_connection__isys_obj__id = isys_cats_organization_list__isys_obj__id ';

        if ($l_obj_id > 0) {
            $l_sql .= ' WHERE isys_cats_organization_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        }

        return $this->retrieve($l_sql . ';')
            ->get_row_value('count');
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   integer $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @see     isys_cmdb_dao_category::get_data()
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address FROM isys_obj
			LEFT JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
			INNER JOIN isys_cats_organization_list ON isys_connection__isys_obj__id = isys_cats_organization_list__isys_obj__id
			WHERE TRUE " . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_person_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed  $p_obj_id
     * @param   string $p_alias
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id);
            } else {
                $l_sql = ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
            }
        }

        return $l_sql;
    }

    public function getPersonsForOrganisation($row)
    {
        $objId = ($row['isys_cats_organization_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $sql = "SELECT GROUP_CONCAT(isys_obj__title SEPARATOR ', ') as persons
                FROM isys_cats_person_list
                INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
                INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id
                WHERE isys_connection__isys_obj__id = ".$this->convert_sql_id($objId)."
                GROUP BY isys_connection__isys_obj__id;";

        $result = $this->get_database_component()->retrieveArrayFromResource($this->get_database_component()->query($sql));

        return $result[0]['persons'];
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_object' => new DynamicProperty(
                'LC__CMDB__CATS__ORGANIZATION_PERSONS',
                'isys_cats_organization_list__isys_obj__id',
                'isys_cats_organization_list',
                [
                    $this,
                    'getPersonsForOrganisation'
                ]
            ),
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'object'  => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ORGANIZATION_PERSONS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_person_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_person_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_person_list',
                            'LEFT',
                            'isys_cats_person_list__isys_connection__id',
                            'isys_cats_person_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_person_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__ORGANIZATION_PERSON__OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__MULTISELECTION => true,
                        isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER     => "C__CATS__PERSON;C__CATS__PERSON_MASTER",
                        isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                        isys_popup_browser_object_ng::C__DATARETRIEVAL  => new isys_callback([
                            'isys_cmdb_dao_category_s_organization_person',
                            'callback_property_object'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'contact' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CONTACT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contact'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__isys_obj__id'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'organization_property_contact'
                    ]
                ]
            ])
        ];
    }

    /**
     *
     * @param   array   $p_objects
     * @param   integer $p_direction
     * @param   string  $p_table
     *
     * @return  boolean
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        if (!empty($p_objects)) {
            foreach ($p_objects as $l_val) {
                $this->detach_person($l_val);
            }

            unset($_POST["id"]);
        }

        return true;
    }

    /**
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save($p_object_id, [$p_category_data['properties']['object'][C__DATA__VALUE]]);
            }
        }

        return $l_indicator;
    }

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    }

    /**
     *
     * @param   integer $p_obj_id
     * @param   array   $p_persons
     *
     * @return  boolean
     */
    public function save($p_obj_id, $p_persons = [])
    {
        $l_data = $this->get_data(null, $p_obj_id);
        $l_current_persons = [];
        while ($l_row = $l_data->get_row()) {
            if ($l_row["isys_connection__isys_obj__id"]) {
                $l_current_persons[$l_row["isys_cats_person_list__isys_obj__id"]] = $l_row["isys_cats_organization_list__id"];
            }
        }

        if ($p_obj_id) {
            foreach ($p_persons as $l_person) {
                if (is_numeric($l_person) && !isset($l_current_persons[$l_person])) {
                    $this->attach_person($l_person, $p_obj_id);
                }
            }
        }

        return true;
    }

    /**
     * Save global category monitor element.
     *
     * @param   integer $p_object_id
     * @param   array   $p_objects
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_existing = [];
        $l_save = [];

        // Select all items from the database-table for deleting them.
        $l_res = $this->get_data(null, $p_object_id);

        while ($l_row = $l_res->get_row()) {
            $l_existing[] = $l_row['isys_obj__id'];

            if (!in_array($l_row['isys_obj__id'], $p_objects)) {
                // Get the connection ID to delete.
                $l_conn_sql = 'SELECT isys_cats_person_list__isys_connection__id
					FROM isys_cats_person_list AS pl
					WHERE pl.isys_cats_person_list__isys_obj__id = ' . $this->convert_sql_id($l_row['isys_obj__id']) . ' LIMIT 1;';

                $l_conn_id = $this->retrieve($l_conn_sql)
                    ->get_row_value('isys_cats_person_list__isys_connection__id');

                if ($l_conn_id > 0) {
                    $this->update('DELETE FROM isys_connection WHERE isys_connection__id = ' . $this->convert_sql_id($l_conn_id) . ' LIMIT 1;');
                }
            }
        }

        if (count($p_objects) > 0) {
            foreach ($p_objects as $l_person) {
                // But don't insert any items, that already exist!
                if (!in_array($l_person, $l_existing)) {
                    if ($l_person > 0) {
                        // Create the new items.
                        $l_save[] = $l_person;
                    }
                }
            }

            if (count($l_save) > 0) {
                return $this->save($p_object_id, $l_save);
            }
        }

        return null;
    }

    /**
     * Executes the query to create the category entry
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_description
     *
     * @return  integer
     * @author Dennis Blümer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus = null, $p_description = null)
    {
        return -1;
    } /// function

    /**
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function detach_person($p_id)
    {
        $l_person_data = new isys_cmdb_dao_category_s_person_master($this->m_db);
        $l_dao_con = new isys_cmdb_dao_connection($this->m_db);
        $l_data = $l_person_data->get_data_by_object($p_id)
            ->__to_array();
        $l_return = true;
        if ($l_dao_con->update_connection($l_data['isys_cats_person_list__isys_connection__id'], null)) {
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
            $l_dao_relation->delete_relation($l_data["isys_cats_person_list__isys_catg_relation_list__id"]);

            $l_return = $this->apply_update();
        }

        return $l_return;
    }

    public function attach_person($p_id, $p_orga_id)
    {
        $l_dao = new isys_cmdb_dao_connection($this->m_db);
        $l_person_data = new isys_cmdb_dao_category_s_person_master($this->m_db);
        $l_data = $l_person_data->get_data_by_object($p_id)
            ->__to_array();
        $l_return = true;
        if ($l_data["isys_cats_person_list__id"] > 0) {
            $l_sql = "UPDATE isys_cats_person_list SET" . " isys_cats_person_list__isys_connection__id = " . $this->convert_sql_id($l_dao->add_connection($p_orga_id)) .
                " WHERE isys_cats_person_list__id = " . $this->convert_sql_id($l_data["isys_cats_person_list__id"]) . ';';

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL .= $l_sql;
                $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
                $l_dao_relation->handle_relation(
                    $l_data["isys_cats_person_list__id"],
                    "isys_cats_person_list",
                    defined_or_default('C__RELATION_TYPE__ORGANIZATION'),
                    $l_data["isys_cats_person_list__isys_catg_relation_list__id"],
                    $p_orga_id,
                    $p_id
                );

                $l_return = $this->apply_update();
            }
        }

        return $l_return;
    }
}
