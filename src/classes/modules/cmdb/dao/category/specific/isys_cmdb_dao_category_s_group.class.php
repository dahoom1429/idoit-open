<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: specific category for groups
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @todo        There should be a single method "get_group_objects" which retrieves the object IDs according to the set type!
 */
class isys_cmdb_dao_category_s_group extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'group';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'object';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

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
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'object';

    /**
     * Field for the object id
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_cats_group_list__isys_obj__id';

    /**
     * Get data method.
     *
     * @todo    generic method inside isys_cmdb_dao_category_s
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT isys_connection__isys_obj__id, me.*, isys_cats_group_list.*,
			other.isys_obj__id AS `connected_id`, other.isys_obj__title AS `connected_title`,
			other.isys_obj__isys_obj_type__id AS `connected_type_id`,
			isys_obj_type__title AS `connected_type`, other.isys_obj__sysid AS `connected_sysid`
			FROM isys_cats_group_list
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_group_list__isys_connection__id
			INNER JOIN isys_obj other ON isys_connection__isys_obj__id = other.isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = other.isys_obj__isys_obj_type__id
			INNER JOIN isys_obj me ON me.isys_obj__id = isys_cats_group_list__isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null && $p_cats_list_id !== 'FALSE') {
            $l_sql .= ' AND isys_cats_group_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if (!is_null($p_status)) {
            $l_sql .= ' AND isys_cats_group_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_cats_group_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_cats_group_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
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
            'object'      => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_group_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_group',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_group',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_group_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_group_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_cats_group_list',
                        'isys_cats_group_list__id',
                        'isys_cats_group_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_group_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_group_list', 'LEFT', 'isys_cats_group_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_cats_group_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__GROUP__OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__MULTISELECTION => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'object_type' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__OBJTYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'connected_type'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__GROUP__OBJECT_TYPE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'translate'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_group_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__GROUP', 'C__CATS__GROUP')
                ],
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
                $p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_category_data['properties']['object'][C__DATA__VALUE], '');
                if ($p_category_data['data_id'] > 0) {
                    $l_indicator = true;
                }
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Method for retrieving connected objects.
     *
     * @param   integer $p_group_id
     *
     * @return  isys_component_dao_result
     */
    public function get_connected_objects($p_group_id, $p_cRecStatus = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = "SELECT isys_obj__id, isys_obj__title, isys_obj_type__id, isys_obj_type__title, isys_connection__isys_obj__id, isys_cats_group_list__id, isys_cats_group_list__isys_catg_relation_list__id
			FROM isys_cats_group_list
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_group_list__isys_connection__id
			INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id($p_group_id);

        if ($p_cRecStatus !== null) {
            $l_sql .= " AND isys_cats_group_list__status = " . $this->convert_sql_int($p_cRecStatus);
        }

        $l_sql .= ";";

        return $this->retrieve($l_sql);
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID, $p_description)
    {
        $l_connection_id = isys_cmdb_dao_connection::instance($this->m_db)
            ->update_connection($this->get_data($p_cat_level)
                ->get_row_value('isys_cats_group_list__isys_connection__id'), $p_connectedObjID);

        $l_sql = "UPDATE isys_cats_group_list SET
			isys_cats_group_list__isys_connection__id = " . $this->convert_sql_id($l_connection_id) . ",
			isys_cats_group_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_group_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_cats_group_list__id = " . $this->convert_sql_id($p_cat_level);

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Save method.
     *
     * @return  null
     */
    public function save_element()
    {
        return null;
    }

    /**
     * @param int $p_cat_level
     * @param int $p_new_id
     *
     * @return NULL
     * @throws Exception
     * @throws isys_exception_dao
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = false;
        $l_members = $l_delete = [];

        /* Check if theres enough data to handle.. */
        if (is_array($p_objects)) {
            $l_dao = $this->get_data(null, $p_object_id);
            while ($l_row = $l_dao->get_row()) {
                if (!in_array($l_row["isys_connection__isys_obj__id"], $p_objects)) {
                    $l_delete[$l_row["isys_connection__isys_obj__id"]] = $l_row["isys_cats_group_list__id"];
                }

                $l_members[$l_row["isys_connection__isys_obj__id"]] = $l_row["isys_connection__isys_obj__id"];
            }

            /* Iterate through objects */
            foreach ($p_objects as $l_popup_object) {
                if (!isset($l_members[$l_popup_object]) && !$l_members[$l_popup_object]) {
                    $l_id = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_popup_object, "");
                }
                unset($l_members[$l_popup_object]);
            }

            if (is_array($l_delete)) {
                $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                foreach ($l_delete as $l_deleteObj => $l_delete_id) {
                    $l_relation_id = $this->get_data_by_id($l_delete_id)
                        ->get_row_value('isys_cats_group_list__isys_catg_relation_list__id');
                    if ($l_relation_id > 0) {
                        $l_dao_relation->delete_relation($l_relation_id);
                    }

                    $l_delete_sql = "DELETE FROM isys_cats_group_list WHERE isys_cats_group_list__id = " . $this->convert_sql_id($l_delete_id) . ';';
                    $this->m_strLogbookSQL .= $l_delete_sql;
                    $this->update($l_delete_sql);
                }
            }

            $this->apply_update();
        }

        return $l_id;
    }

    /**
     * Executes the query to create the category entry
     *
     * @param int    $p_objID
     * @param int    $p_newRecStatus
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_connectedObjID, $p_description)
    {
        $l_connection_dao = new isys_cmdb_dao_connection($this->m_db);

        $l_strSql = "INSERT INTO isys_cats_group_list SET " . "isys_cats_group_list__description   = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_group_list__isys_obj__id  = " . $this->convert_sql_id($p_objID) . ", " . "isys_cats_group_list__isys_connection__id  = " .
            $this->convert_sql_id($l_connection_dao->add_connection($p_connectedObjID)) . ", " . "isys_cats_group_list__status        = " .
            $this->convert_sql_id($p_newRecStatus) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $this->get_last_query();

            $l_id = $this->get_last_insert_id();

            $l_data = $this->get_data($l_id)
                ->__to_array();
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_dao_relation->handle_relation(
                $l_id,
                "isys_cats_group_list",
                defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'),
                $l_data["isys_cats_group_list__isys_catg_relation_list__id"],
                $p_connectedObjID,
                $p_objID
            );

            return $l_id;
        } else {
            return false;
        }
    }

    /**
     * Gets group type dynamic/static
     *
     * @param $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_group_type($p_obj_id)
    {
        $l_sql = 'SELECT isys_cats_group_type_list__type
			FROM isys_cats_group_type_list
			WHERE isys_cats_group_type_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        return (int)$this->retrieve($l_sql)
            ->get_row_value('isys_cats_group_type_list__type') ?: 0;
    }
}
