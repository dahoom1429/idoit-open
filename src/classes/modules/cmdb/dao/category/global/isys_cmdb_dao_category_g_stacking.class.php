<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: Global category for stacking.
 *
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @copyright     synetics GmbH
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 */
class isys_cmdb_dao_category_g_stacking extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'stacking';

    /**
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var  boolean
     */
    protected $m_has_relation = true;

    /**
     * Flag which defines if the category is a multivalued category.
     *
     * @var  bool
     */
    protected $m_multivalued = true;

    /**
     * Flag which defines if the category is only a list with an object browser
     *
     * @var bool
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var  string
     */
    protected $m_object_browser_property = 'assigned_object';

    /**
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_stacking_list__isys_obj__id';

    /**
     * Return Category Data
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_stacking_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_stacking_list__isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_stacking_list__isys_connection__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_catg_stacking_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if (!empty($p_status)) {
            $l_sql .= ' AND isys_catg_stacking_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    protected function properties()
    {
        return [
            'assigned_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned chassis'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_stacking_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__STACKING'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_stacking',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_stacking']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                if (is_array($p_category_data['properties']['assigned_object'][C__DATA__VALUE])) {
                    $this->create_stacking($p_object_id, $p_category_data['properties']['assigned_object'][C__DATA__VALUE]);
                } else {
                    return $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_category_data['properties']['assigned_object'][C__DATA__VALUE]);
                }
            }
        }

        return true;
    }

    /**
     * Gets all assigned objects for the object browser.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_connected_objects($p_obj_id)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj_type__id, isys_obj_type__title, isys_connection__isys_obj__id
			FROM isys_catg_stacking_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_stacking_list__isys_connection__id
			INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_stacking_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Empty save element function
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  null
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        return null;
    }

    /**
     * Create element function.
     *
     * @param   integer $p_object_id
     * @param   array   $p_objects
     *
     * @return  null
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        // Check if there is enough data to handle.
        if (is_array($p_objects)) {
            $l_objects = array_merge([$p_object_id], $p_objects);

            // Iterate through objects.
            foreach ($l_objects as $l_object_id) {
                $l_arr_objects = $l_objects;
                unset($l_arr_objects[array_search($l_object_id, $l_arr_objects)]);

                $this->create_stacking($l_object_id, array_values($l_arr_objects));
            }

            $this->apply_update();
        }

        return null;
    }

    /**
     * Wrapper function in which all assigned objects gets the same assignments to the referenced objects
     *
     * @param   integer $p_object_id
     * @param   array   $p_stacking_objects
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @return  integer
     */
    public function create_stacking($p_object_id, $p_stacking_objects)
    {
        $l_delete = $l_members = [];
        $l_res = $this->get_data(null, $p_object_id);

        while ($l_row = $l_res->get_row()) {
            if (!in_array($l_row["isys_connection__isys_obj__id"], $p_stacking_objects) && $l_row["isys_connection__isys_obj__id"] > 0) {
                $l_data = $this->get_data(null, $l_row['isys_connection__isys_obj__id'], 'AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_object_id))
                    ->get_row();

                $l_delete[$l_row["isys_connection__isys_obj__id"]] = [
                    $l_row["isys_catg_stacking_list__id"]  => $l_row["isys_catg_stacking_list__isys_catg_relation_list__id"],
                    $l_data["isys_catg_stacking_list__id"] => $l_data["isys_catg_stacking_list__isys_catg_relation_list__id"]
                ];
            }

            $l_members[$l_row["isys_connection__isys_obj__id"]] = $l_row["isys_connection__isys_obj__id"];
        }

        $l_return = null;

        foreach ($p_stacking_objects AS $l_object_id) {
            if (!isset($l_members[$l_object_id])) {
                // Object self.
                $l_id = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_object_id, "");

                if ($l_object_id == $p_object_id) {
                    $l_return = $l_id;
                }

                if ($l_id != false) {
                    unset($l_changes, $l_changes_compressed);
                }
            }

            unset($l_members[$l_object_id]);
        }

        if (is_array($l_delete) && count($l_delete)) {
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            foreach ($l_delete as $l_deleteObj => $l_delete_data) {
                $l_delete_data = array_filter($l_delete_data);

                if (count($l_delete_data)) {
                    $l_relation_ids = array_values($l_delete_data);

                    $this->update("DELETE FROM isys_catg_stacking_list WHERE isys_catg_stacking_list__id " . $this->prepare_in_condition(array_keys($l_delete_data)) . ";");

                    if (count($l_relation_ids)) {
                        foreach ($l_relation_ids AS $l_relation_id) {
                            $l_relation_dao->delete_relation($l_relation_id);
                        }
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Creates a new entry for the current object.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   integer $p_assigned_obj
     * @param   string  $p_description
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_assigned_obj, $p_description = '')
    {
        $l_con_id = isys_cmdb_dao_connection::instance($this->m_db)
            ->add_connection($p_assigned_obj);

        $l_query = 'INSERT INTO isys_catg_stacking_list SET
			isys_catg_stacking_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
			isys_catg_stacking_list__status = ' . $this->convert_sql_int($p_status) . ',
			isys_catg_stacking_list__isys_connection__id = ' . $this->convert_sql_id($l_con_id) . ',
			isys_catg_stacking_list__description = ' . $this->convert_sql_text($p_description) . ';';

        if ($this->update($l_query) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $this->get_last_query();

            $l_id = $this->get_last_insert_id();

            // Create implicit relation.
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_relation_dao->handle_relation($l_id, "isys_catg_stacking_list", defined_or_default('C__RELATION_TYPE__STACKING'), null, $p_obj_id, $p_assigned_obj);

            return $l_id;
        } else {
            return false;
        }
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
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID, $p_description)
    {
        $l_data = $this->get_data($p_cat_level)
            ->get_row_value('isys_catg_stacking_list__isys_connection__id');

        $l_connection_id = isys_cmdb_dao_connection::instance($this->m_db)
            ->update_connection($l_data, $p_connectedObjID);

        $l_strSql = "UPDATE isys_catg_stacking_list SET
			isys_catg_stacking_list__isys_connection__id = " . $this->convert_sql_id($l_connection_id) . ",
			isys_catg_stacking_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_stacking_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_catg_stacking_list__id = " . $this->convert_sql_id($p_cat_level) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $this->get_last_query();

            // Create implicit relation.
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_data = $this->get_data($p_cat_level)
                ->get_row();

            $l_relation_dao->handle_relation($p_cat_level, "isys_catg_stacking_list", defined_or_default('C__RELATION_TYPE__STACKING'),
                $l_data["isys_catg_stacking_list__isys_catg_relation_list__id"], $l_data["isys_catg_stacking_list__isys_obj__id"], $p_connectedObjID);

            return true;
        }

        return false;
    }
}