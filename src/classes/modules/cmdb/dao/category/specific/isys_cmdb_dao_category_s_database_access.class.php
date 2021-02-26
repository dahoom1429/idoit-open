<?php
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: specific category database access.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_access extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_access';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'access';

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
     * Flag which defines if the category is only a list with an object browser
     *
     * @var bool
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'assigned_object';

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_database_access_list__isys_obj__id';

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT assign.isys_obj__title AS assignment_title, assign.isys_obj__isys_obj_type__id AS assignment_type, assign.isys_obj__sysid AS assignment_sysid, self.*, isys_cats_database_access_list.*, isys_connection.*
			FROM isys_cats_database_access_list
			INNER JOIN isys_obj self ON self.isys_obj__id = isys_cats_database_access_list__isys_obj__id
			LEFT OUTER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
			LEFT OUTER JOIN isys_obj assign ON isys_connection__isys_obj__id = assign.isys_obj__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_database_access_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_database_access_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Creates the condition to the object table
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                return ' AND (self.isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                return ' AND (self.isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return '';
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'access' => (new ObjectBrowserConnectionProperty(
                'C__CMDB__CATS__DATABASE_ACCESS__ACCESS',
                'LC__CMDB__CATS__MAINTENANCE_LINKED_OBJECT_LIST',
                'isys_cats_database_access_list__isys_connection__id',
                'isys_cats_database_access_list',
                [
                    'isys_export_helper',
                    'relation_connection'
                ],
                'C__CATS__APPLICATION;C__CATS__LICENCE;C__CATS__OPERATING_SYSTEM;C__CATS__CLUSTER_SERVICE;C__CATS__DBMS;C__CATS__DATABASE_SCHEMA;C__CATS__DATABASE_INSTANCE;C__CATS__MIDDLEWARE'
            ))->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__DATABASE_ACCESS')
            )->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_s_database_access',
                    'callback_property_relation_handler'
                ], ['isys_cmdb_dao_category_s_database_access'])
            )->mergePropertyUiParams(
                [
                    'name'                                            => 'C__CMDB__CATS__DATABASE_ACCESS__ACCESS',
                    isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                    isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
                    isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
                    isys_popup_browser_object_ng::C__DATARETRIEVAL    => [
                        ['isys_cmdb_dao_category_s_database_access', 'get_data_by_object'],
                        $_GET[C__CMDB__GET__OBJECT],
                        [
                            "isys_connection__id",
                            "assignment_title",
                            "assignment_type",
                            "assignment_sysid"
                        ]
                    ],
                    isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                    isys_popup_browser_object_ng::C__CAT_FILTER       => "C__CATS__APPLICATION;C__CATS__LICENCE;C__CATS__OPERATING_SYSTEM;C__CATS__CLUSTER_SERVICE;C__CATS__DBMS;C__CATS__DATABASE_SCHEMA;C__CATS__DATABASE_INSTANCE;C__CATS__MIDDLEWARE",
                    isys_popup_browser_object_ng::C__SECOND_LIST      => [
                        'isys_cmdb_dao_category_s_database_access::object_browser',
                        ['typefilter' => defined_or_default('C__RELATION_TYPE__SOFTWARE')]
                    ]
                ]
            )
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
            /* Get relation object if exists otherwise create new relation object */
            $l_dao_rel = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_dao_app = new isys_cmdb_dao_category_g_application($this->get_database_component());
            $l_condition = " AND isys_catg_relation_list__isys_obj__id__master = " . $this->convert_sql_id($p_category_data['properties']['access'][C__DATA__VALUE][0]) .
                " AND isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($p_category_data['properties']['access'][C__DATA__VALUE][1]) .
                " AND isys_catg_relation_list__isys_relation_type__id = " . defined_or_default('C__RELATION_TYPE__SOFTWARE');
            $l_rel = $l_dao_rel->get_data(null, null, $l_condition)
                ->get_row();
            if ($l_rel) {
                $l_rel_obj_id = $l_rel['isys_catg_relation_list__isys_obj__id'];
            } else {
                $l_last_id = $l_dao_app->create(
                    $p_category_data['properties']['access'][C__DATA__VALUE][0],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['access'][C__DATA__VALUE][1],
                    null
                );
                $l_app_data = $l_dao_app->get_data($l_last_id)
                    ->get_row();
                $l_new_rel = $l_dao_rel->get_data($l_app_data['isys_catg_application_list__isys_catg_relation_list__id'])
                    ->get_row();
                $l_rel_obj_id = $l_new_rel['isys_catg_relation_list__isys_obj__id'];
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                if ($p_category_data['data_id'] = $this->create($p_object_id, $l_rel_obj_id, C__RECORD_STATUS__NORMAL)) {
                    $l_indicator = true;
                }
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save($p_category_data['data_id'], $l_rel_obj_id, C__RECORD_STATUS__NORMAL);
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_connectedObjID, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "UPDATE isys_cats_database_access_list " . "SET " . "isys_cats_database_access_list__isys_connection__id = " .
            $this->convert_sql_id($this->handle_connection($p_id, $p_connectedObjID)) . ", " . "isys_cats_database_access_list__status = " . $this->convert_sql_id($p_status) .
            " " .

            "WHERE isys_cats_database_access_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_strSql)) {
            if ($this->apply_update()) {
                $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

                $l_data = $this->get_data($p_id)
                    ->__to_array();

                $l_dao_relation->handle_relation(
                    $p_id,
                    "isys_cats_database_access_list",
                    defined_or_default('C__RELATION_TYPE__DATABASE_ACCESS'),
                    $l_data["isys_cats_database_access_list__isys_catg_relation_list__id"],
                    $l_data["isys_cats_database_access_list__isys_obj__id"],
                    $p_connectedObjID
                );

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_object_id
     * @param   integer $p_connectedObjID
     * @param   integer $p_status
     * @param   string  $p_commentary
     *
     * @return  mixed  Integer of the newly created ID on success, boolean false on failure.
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_object_id, $p_connectedObjID, $p_status = C__RECORD_STATUS__NORMAL, $p_commentary = '')
    {
        $l_sql = "INSERT INTO isys_cats_database_access_list SET
			isys_cats_database_access_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ",
			isys_cats_database_access_list__isys_connection__id = " . $this->convert_sql_id(isys_factory::get_instance('isys_cmdb_dao_connection', $this->m_db)
                ->add_connection($p_connectedObjID)) . ",
			isys_cats_database_access_list__status = " . $this->convert_sql_id($p_status) . ",
			isys_cats_database_access_list__description = " . $this->convert_sql_text($p_commentary) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $l_sql;
            $l_id = $this->get_last_insert_id();

            isys_factory::get_instance('isys_cmdb_dao_category_g_relation', $this->m_db)
                ->handle_relation($l_id, "isys_cats_database_access_list", defined_or_default('C__RELATION_TYPE__DATABASE_ACCESS'), null, $p_object_id, $p_connectedObjID);

            return $l_id;
        } else {
            return false;
        }
    }

    /**
     * Save element method, doing nothing.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     * @param   boolean $p_create
     *
     * @return  null
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {
        return null;
    }

    /**
     * Create a new element and deleting unused ones.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_new_id
     *
     * @return  null
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_cmdb_dao_category_s_database_access::create
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_delete = [];

        // Select all items from the database-table for deleting them.
        $l_sql = 'SELECT isys_connection__isys_obj__id, isys_cats_database_access_list__id
			FROM isys_cats_database_access_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
			WHERE isys_cats_database_access_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            // Collect items to delete.
            if (!in_array($l_row['isys_connection__isys_obj__id'], $p_objects)) {
                $l_delete[$l_row['isys_cats_database_access_list__id']] = $l_row['isys_connection__isys_obj__id'];
            }
            $l_current_objects[$l_row['isys_connection__isys_obj__id']] = true;
        }

        // Delete all items with one uber sql.
        if (count($l_delete) > 0) {
            $l_sql = 'DELETE FROM isys_cats_database_access_list ' . 'WHERE isys_cats_database_access_list__id ' . $this->prepare_in_condition(array_flip($l_delete)) . ';';

            $this->m_strLogbookSQL .= $l_sql;

            // And delete.
            $this->update($l_sql);
        }

        // Now insert new items.
        if (is_array($p_objects)) {
            foreach ($p_objects as $l_object) {
                if ($l_object > 0 && !$this->connection_exists($p_object_id, $l_object)) {
                    $this->create($p_object_id, $l_object);
                }
            }
        }

        return null;
    }

    /**
     * Checks if a connection to a database schema object exists.
     *
     * @param   integer $p_object
     * @param   integer $p_schema_object
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function connection_exists($p_object, $p_schema_object)
    {
        return (count($this->get_data(null, $p_object, "AND isys_connection__isys_obj__id = " . $this->convert_sql_text($p_schema_object ?: 0) . ' ')) > 0);
    }

    /**
     * Deletes connection.
     *
     * @param   integer $p_object
     * @param   integer $p_connection_id
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function delete_connection($p_object = null, $p_connection_id = null)
    {
        if (is_null($p_object) && is_null($p_connection_id)) {
            return false;
        }
        $l_row = [];
        $l_sql = "DELETE FROM isys_cats_database_access_list WHERE ";

        if ($p_object) {
            $l_row = $this->get_data(null, null, "AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_object), null, C__RECORD_STATUS__NORMAL)
                ->__to_array();
            $l_sql .= "isys_cats_database_access_list__id = " . $this->convert_sql_id($l_row["isys_cats_database_access_list__id"]);
        } elseif ($p_connection_id) {
            $l_row = $this->get_data(null, null, "AND isys_connection__id = " . $this->convert_sql_id($p_connection_id), null, C__RECORD_STATUS__NORMAL)
                ->__to_array();
            $l_sql .= "isys_cats_database_access_list__isys_connection__id = " . $this->convert_sql_id($p_connection_id);
        }

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
            $l_dao_relation->delete_relation($l_row["isys_cats_database_access_list__isys_catg_relation_list__id"]);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the access-list entrys by the object-id.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_accesslist_by_object($p_id)
    {
        $l_sql = 'SELECT obj.isys_obj__id, obj.isys_obj__title FROM isys_cats_database_access_list AS al ' .
            'LEFT JOIN isys_connection AS conn ON conn.isys_connection__id = al.isys_cats_database_access_list__isys_connection__id ' .
            'LEFT JOIN isys_obj AS obj ON conn.isys_connection__isys_obj__id = obj.isys_obj__id ' . 'WHERE al.isys_cats_database_access_list__isys_obj__id = ' .
            $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return array|string
     * @throws \idoit\Exception\JsonException
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                // Create a new instance of this class, with database.
                $l_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

                // Get the relations by the object-id.
                $l_relations = $l_dao->get_related_objects($_GET[C__CMDB__GET__OBJECT], $p_parameters['typefilter']);

                while ($l_row = $l_relations->get_row()) {
                    $l_obj_dao = new isys_cmdb_dao($this->get_database_component());
                    $l_obj_res = $l_obj_dao->get_type_by_object_id($l_row['isys_catg_relation_list__isys_obj__id']);
                    $l_obj_row = $l_obj_res->get_row();

                    if ($l_obj_row['isys_obj__isys_obj_type__id'] == defined_or_default('C__OBJTYPE__RELATION')) {
                        $l_return[] = [
                            '__checkbox__'                          => $l_row['isys_obj__id'],
                            $language->get('LC__CMDB__CATP__TITLE') => $l_row['isys_obj__title'],
                            $language->get('LC__CMDB__CATG__TYPE')  => $language->get('LC__CMDB__OBJTYPE__RELATION'),
                            $language->get('LC__CMDB__CATG__TYPE')  => $language->get($l_obj_row['isys_obj_type__title'])
                        ];
                    }
                }

                return json_encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $l_params = (array)isys_format_json::decode(base64_decode($_POST['params']));

                // We can't preselect a category or first-element. So just preselect elements for the second-list.
                $l_cat_res = $this->get_accesslist_by_object($l_params[isys_popup_browser_object_ng::C__DATARETRIEVAL][1]);

                while ($l_row = $l_cat_res->get_row()) {
                    // @see  ID-6220  Also return a 'first' selection.
                    $l_return['first'][] = (int)$l_row['isys_obj__id'];

                    $l_return['second'][] = [
                        $l_row['isys_obj__id'],
                        $l_row['isys_obj__title'],
                        $language->get('LC__CMDB__OBJTYPE__RELATION'),
                        '',
                    ];
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = $this->get_object($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_obj__id'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title'])
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE')
                    ],
                    'data'   => $preselection
                ];
        }
    }
}
