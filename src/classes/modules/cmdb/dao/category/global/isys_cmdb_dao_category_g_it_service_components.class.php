<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for IT service components
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_it_service_components extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'it_service_components';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';

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
    protected $m_object_browser_property = 'connected_object';

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_its_components_list__isys_obj__id';

    /**
     * Source table of this category
     *
     * @var string
     */
    protected $m_table = 'isys_catg_its_components_list';

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT isys_catg_its_components_list.*,
			isys_connection.*,
			me.isys_obj__id, me.isys_obj__title, me.isys_obj__status, me.isys_obj__isys_obj_type__id, me.isys_obj__sysid,
			itsc.isys_obj__title itsc_title, itsc.isys_obj__status itsc_status, itsc.isys_obj__sysid itsc_sysid, itsc.isys_obj__isys_obj_type__id AS itsc_type
			FROM isys_catg_its_components_list
			INNER JOIN isys_obj me ON isys_catg_its_components_list__isys_obj__id = me.isys_obj__id
			INNER JOIN isys_connection ON isys_catg_its_components_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_obj itsc ON itsc.isys_obj__id = isys_connection__isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= "AND isys_catg_its_components_list__id = " . $this->convert_sql_id($p_catg_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_catg_its_components_list__status = " . $this->convert_sql_int($p_status) . " ";
        }

        return $this->retrieve($l_sql . 'ORDER BY itsc.isys_obj__isys_obj_type__id ASC;');
    }

    /**
     * Preselection for the Object Browser. Which checks the current status if no status has been delivered.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_object($p_obj_id, $p_condition = null, $p_status = null)
    {
        if ($p_status === null && isset($_POST['cRecStatus']) && is_numeric($_POST['cRecStatus'])) {
            $p_status = (int)$_POST['cRecStatus'];
        }

        return $this->get_data(null, $p_obj_id, $p_condition, null, $p_status);
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
                $l_sql = ' AND (isys_catg_its_components_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_its_components_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SHARE_ACCESS__ASSIGNED_OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_its_components_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__IT_SERVICE_COMPONENT'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_it_service_components',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_it_service_components',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_obj__title
                              FROM isys_catg_its_components_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_catg_its_components_list__isys_connection__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_its_components_list',
                        'isys_catg_its_components_list__id',
                        'isys_catg_its_components_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_its_components_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_its_components_list',
                            'LEFT',
                            'isys_catg_its_components_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_its_components_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IT_SERVICE_COMPONENTS__CONNECTED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType'                                       => 'browser_object_relation',
                        isys_popup_browser_object_relation::C__MULTISELECTION  => true,
                        isys_popup_browser_object_relation::C__RELATION_FILTER => "C__RELATION_TYPE__SOFTWARE;C__RELATION_TYPE__CLUSTER_SERVICE",
                        isys_popup_browser_object_relation::C__CAT_FILTER      => "C__CATG__IT_SERVICE_COMPONENTS"
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'objtype'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__REPORT__FORM__OBJECT_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__isys_obj_type__id',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'itsc_type',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'itsc',

                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__IT_SERVICE_COMPONENTS__OBJTYPE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'obj_type'
                    ]
                ]
            ])
        ];
    }

    /**
     * Rank record method of it-service components.
     *
     * @param   array   $p_objects
     * @param   integer $p_direction
     * @param   string  $p_table
     *
     * @return  boolean
     * @throws  isys_exception_general
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__RECYCLE:

                if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__ARCHIVED) {
                    $l_status = C__RECORD_STATUS__NORMAL;
                } elseif (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__DELETED) {
                    $l_status = C__RECORD_STATUS__ARCHIVED;
                }
                break;

            case C__NAVMODE__ARCHIVE:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;

            case C__NAVMODE__DELETE:
                $l_status = C__RECORD_STATUS__DELETED;
                break;

            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (is_array($p_objects) && count($p_objects)) {
                    $result = $this->get_data(null, null, ' AND isys_catg_its_components_list__id ' . $this->prepare_in_condition($p_objects));

                    while ($row = $result->get_row()) {
                        if ($row['isys_catg_its_components_list__status'] == C__RECORD_STATUS__DELETED) {
                            $this->delete_its_relation($row['isys_catg_its_components_list__id'], $_GET[C__CMDB__GET__OBJECT]);
                        }
                        parent::rank_record($row['isys_catg_its_components_list__id'], $p_direction, 'isys_catg_its_components_list');
                    }
                }

                return true;
                break;
        }

        parent::rank_records($p_objects, $p_direction, "isys_catg_its_components_list");

        foreach ($p_objects as $l_cat_id) {
            $this->set_status($l_cat_id, $_GET[C__CMDB__GET__OBJECT], $l_status);
        }

        return true;
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Add new graphic adapter.
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     * @param   integer $p_connected_obj
     * @param   string  $p_description
     *
     * @return  mixed
     */
    public function create($p_object_id, $p_status, $p_connected_obj, $p_description = null)
    {
        $l_dao_con = new isys_cmdb_dao_connection($this->m_db);

        $l_sql = "INSERT INTO isys_catg_its_components_list SET
			isys_catg_its_components_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_its_components_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_its_components_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ",
			isys_catg_its_components_list__isys_connection__id = " . $this->convert_sql_id($l_dao_con->add_connection($p_connected_obj)) . ";";

        if ($this->update($l_sql)) {
            if ($this->apply_update()) {
                $this->m_strLogbookSQL .= $l_sql;

                $l_last_id = $this->get_last_insert_id();
                $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
                $l_dao_relation->handle_relation($l_last_id, "isys_catg_its_components_list", defined_or_default('C__RELATION_TYPE__IT_SERVICE_COMPONENT'), null, $p_connected_obj, $p_object_id);

                $l_sql = 'SELECT isys_catg_its_components_list__isys_catg_relation_list__id, isys_catg_relation_list__isys_obj__id__itservice
					FROM isys_catg_its_components_list
					INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_its_components_list__isys_catg_relation_list__id
					WHERE isys_catg_its_components_list__id = ' . $this->convert_sql_id($l_last_id) . ';';

                $l_data = $this->retrieve($l_sql)
                    ->get_row();
                $l_relation_id = $l_data['isys_catg_its_components_list__isys_catg_relation_list__id'];
                $l_it_service = $l_data['isys_catg_relation_list__isys_obj__id__itservice'];

                if ($l_it_service != $p_object_id) {
                    $l_dao_relation->set_it_service($l_relation_id, $p_object_id);
                    $l_dao_relation->apply_update();
                }

                return $l_last_id;
            }
        }

        return false;
    }

    /**
     * Updates an existing entry.
     *
     * @param   integer $p_id
     * @param   integer $p_status
     * @param   integer $p_connected_obj
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_id, $p_status, $p_connected_obj, $p_description = null)
    {
        if (is_numeric($p_id)) {
            $l_sql = "UPDATE isys_catg_its_components_list
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_its_components_list__isys_connection__id
				SET isys_connection__isys_obj__id = " . $this->convert_sql_id($p_connected_obj) . ",
				isys_catg_its_components_list__status = " . $this->convert_sql_int($p_status) . ",
				isys_catg_its_components_list__description = " . $this->convert_sql_text($p_description) . "
				WHERE isys_catg_its_components_list__id = " . $this->convert_sql_id($p_id) . ";";

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL .= $l_sql;

                if ($this->apply_update()) {
                    $l_catdata = $this->get_data($p_id)
                        ->get_row();
                    $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

                    $l_dao_relation->handle_relation(
                        $p_id,
                        "isys_catg_its_components_list",
                        defined_or_default('C__RELATION_TYPE__IT_SERVICE_COMPONENT'),
                        $l_catdata["isys_catg_its_components_list__isys_catg_relation_list__id"],
                        $p_connected_obj,
                        $l_catdata["isys_catg_its_components_list__isys_obj__id"]
                    );

                    $l_dao_relation->set_it_service(
                        $l_catdata["isys_catg_its_components_list__isys_catg_relation_list__id"],
                        $l_catdata["isys_catg_its_components_list__isys_obj__id"]
                    );

                    $l_dao_relation->set_relation_object_status($l_catdata["isys_catg_its_components_list__isys_catg_relation_list__id"], $p_status);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Method for saving elements. Unused.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     * @param   boolean $p_create
     *
     * @return  null
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        return null;
    }

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return mixed|null
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = null;
        $l_currentObjects = [];

        /**
         * 1) Check for delete objects in $l_members
         *  1a) Delete current connection if there is a deleted member
         * 2) Create a currentMember array to check if the entry is already existings afterwards
         */
        $l_current = $this->get_data_by_object($p_object_id);
        while ($l_row = $l_current->get_row()) {
            if (!in_array($l_row["isys_connection__isys_obj__id"], $p_objects)) {
                $this->delete_entry($l_row[$this->m_source_table . '_list__id'], $this->m_source_table . '_list');
            } else {
                $l_currentObjects[$l_row["isys_connection__isys_obj__id"]] = $l_row["isys_connection__isys_obj__id"];
            }
        }

        foreach ($p_objects as $l_object_id) {
            if (is_numeric($l_object_id)) {
                $l_res = $this->get_assigned_object($_GET[C__CMDB__GET__OBJECT], $l_object_id);
                if ($l_res->num_rows() == 0) {
                    $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $l_object_id, "");
                } else {
                    $l_row = $l_res->get_row();
                    $this->save($l_row["isys_catg_its_components_list__id"], C__RECORD_STATUS__NORMAL, $l_object_id, null);
                    $l_id = $l_row["isys_catg_its_components_list__id"];
                }
            }
        }

        return $l_id;
    }

    /**
     * Method for adding a new component.
     *
     * @param   integer $p_itservice_id
     * @param   integer $p_component_id
     *
     * @return  mixed  Integer with the last inserted ID on success, boolean (false) on failure.
     */
    public function add_component($p_itservice_id, $p_component_id)
    {
        if ($this->get_assigned_object($p_itservice_id, $p_component_id)
                ->num_rows() <= 0) {
            return $this->create($p_itservice_id, C__RECORD_STATUS__NORMAL, $p_component_id, "");
        }

        return false;
    }

    public function remove_component($p_object_id, $p_connection_id)
    {
        $l_dao_rel = new isys_cmdb_dao_category_g_relation($this->m_db);
        $l_relation_entries = [];

        $l_sql = "DELETE FROM isys_catg_its_components_list
			WHERE isys_catg_its_components_list__id IN (";

        $l_res = $this->get_data(null, $p_object_id, "AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_connection_id), null, C__RECORD_STATUS__NORMAL);
        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_sql .= $this->convert_sql_id($l_row["isys_catg_its_components_list__id"]) . ',';
                $l_relation_entries[] = $l_row['isys_catg_its_components_list__isys_catg_relation_list__id'];
            }
        } else {
            return false;
        }

        $l_sql = rtrim($l_sql, ',') . ');';

        foreach ($l_relation_entries as $l_id) {
            $l_dao_rel->delete_relation($l_id);
        }

        if ($this->update($l_sql) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $l_sql;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the assigned objects.
     *
     * @param   integer $p_object
     * @param   integer $p_connected_obj
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_object($p_object, $p_connected_obj = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_its_components_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_its_components_list__isys_connection__id
			WHERE isys_catg_its_components_list__isys_obj__id = ' . $this->convert_sql_id($p_object);

        if ($p_connected_obj !== null) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_connected_obj);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for retrieving the assigned object(s).
     *
     * @param   integer $p_object
     * @param   boolean $p_asString
     *
     * @return  mixed
     */
    public function get_assigned_objects_as_string($p_object, $p_asString = false)
    {
        $l_arr = [];
        $l_res = $this->get_assigned_object($p_object);

        while ($l_row = $l_res->get_row()) {
            $l_arr[] = $l_row["isys_connection__isys_obj__id"];
        }

        if ($p_asString) {
            return implode(',', $l_arr);
        } else {
            return $l_arr;
        }
    }

    /**
     *
     * @param  integer $p_cat_id
     * @param  integer $p_obj_id
     * @param  integer $p_status
     */
    private function set_status($p_cat_id, $p_obj_id = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

        $l_catdata = $this->get_data($p_cat_id)
            ->get_row();

        // Set status for it service relation.
        if ($p_obj_id > 0) {
            $l_res = $l_dao_relation->get_data(
                null,
                $l_catdata["isys_connection__isys_obj__id"],
                " AND isys_catg_relation_list__isys_obj__id__itservice = " . $this->convert_sql_id($p_obj_id)
            );
            while ($l_row = $l_res->get_row()) {
                if ($p_status < $l_row["isys_catg_relation_list__status"]) {
                    // Check status for both objects in it service components.
                    if ($this->check_its_component_status($l_row["isys_catg_relation_list__isys_obj__id__master"], $p_obj_id, $p_status) &&
                        $this->check_its_component_status($l_row["isys_catg_relation_list__isys_obj__id__slave"], $p_obj_id, $p_status)) {
                        $l_dao_relation->set_status($l_row["isys_catg_relation_list__id"], $p_status);
                        $l_dao_relation->set_object_status($l_row["isys_catg_relation_list__isys_obj__id"], $p_status);
                    }
                } else {
                    $l_dao_relation->set_status($l_row["isys_catg_relation_list__id"], $p_status);
                    $l_dao_relation->set_object_status($l_row["isys_catg_relation_list__isys_obj__id"], $p_status);
                }
            }

            $this->apply_update();
        }
    }

    /**
     * Method for checking an it-service component status.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_it_service
     * @param   integer $p_status
     *
     * @return  boolean
     */
    private function check_its_component_status($p_obj_id, $p_it_service, $p_status)
    {
        $l_condition = ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' AND isys_catg_its_components_list__status <= ' .
            $this->convert_sql_id($p_status) . ' ';

        $res = $this->get_data(null, $p_it_service, $l_condition);
        return is_countable($res) && !!count($res);
    }

    /**
     * Method for deleting an it-service relation.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    private function delete_its_relation($p_cat_id = null, $p_obj_id = null)
    {
        $database = isys_application::instance()->container->get('database');
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($database);

        $l_catdata = $this->get_data($p_cat_id)
            ->get_row();

        if ($p_obj_id > 0) {
            isys_cmdb_dao_connection::instance($database)->delete($l_catdata['isys_connection__id']);

            $l_res = $l_dao_relation->get_data(
                null,
                $l_catdata["isys_connection__isys_obj__id"],
                " AND isys_catg_relation_list__isys_obj__id__itservice = " . $this->convert_sql_id($p_obj_id)
            );

            while ($l_row = $l_res->get_row()) {
                $l_bRet = $l_dao_relation->delete_relation($l_row["isys_catg_relation_list__id"]);
            }
        }

        return $l_bRet;
    }

    /**
     * Validates property data.
     *
     * @param   array $p_data Associative array of property tags as keys and their values as values.
     * @param   mixed $p_prepend_table_field
     *
     * @return  mixed  Returns true on a successful validation, otherwise an associative array with property tags as keys and error messages as values.
     * @author  Oscar Pohl <opohl@synetics.de>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        if ($p_data['connected_object'] === false) {
            return ['connected_object' => isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID')];
        }

        return parent::validate($p_data);
    }
}
