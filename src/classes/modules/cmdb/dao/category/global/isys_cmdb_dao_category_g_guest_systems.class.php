<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for guest systems
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_guest_systems extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'guest_systems';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__GUEST_SYSTEMS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_virtual_machine_list__isys_obj__id';

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
     * Field for the object id.
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Main table where properties are stored persistently.
     *
     * @var   string
     * @todo  Breaks with developer guidelines!
     */
    protected $m_table = 'isys_catg_virtual_machine_list';

    /**
     * Create method.
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     * @param   integer $p_guest_systems
     * @param   string  $p_description
     * @param   integer $p_runs_on
     *
     * @return  mixed
     */
    public function create($p_object_id, $p_status, $p_guest_systems, $p_description, $p_runs_on = null)
    {
        $l_dao_con = new isys_cmdb_dao_connection($this->m_db);

        $l_sql = 'INSERT INTO isys_catg_virtual_machine_list
            SET isys_catg_virtual_machine_list__status = ' . $this->convert_sql_int($p_status) . ',
            isys_catg_virtual_machine_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_virtual_machine_list__isys_obj__id = ' . $this->convert_sql_id($p_guest_systems) . ',
            isys_catg_virtual_machine_list__vm = 2,
            isys_catg_virtual_machine_list__isys_connection__id = ' . $this->convert_sql_id($l_dao_con->add_connection($p_object_id));

        if (!is_null($p_runs_on) && !empty($p_runs_on)) {
            $l_sql .= ', isys_catg_virtual_machine_list__primary = ' . $this->convert_sql_id($p_runs_on);
        }

        if ($this->update($l_sql . ';')) {
            if ($this->apply_update()) {
                $this->m_strLogbookSQL .= $l_sql . ';';

                $l_last_id = $this->get_last_insert_id();

                // Create implicit relation.
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                $l_relation_dao->handle_relation($l_last_id, "isys_catg_virtual_machine_list", defined_or_default('C__RELATION_TYPE__VIRTUAL_MACHINE'), null, $p_object_id, $p_guest_systems);

                return $l_last_id;
            }
        }

        return false;
    }

    /**
     * Updates an existing.
     *
     * @param   integer $p_id
     * @param   integer $p_status
     * @param   integer $p_connected_obj
     * @param   string  $p_description
     * @param   integer $p_runs_on
     *
     * @return  boolean
     */
    public function save($p_id, $p_status, $p_connected_obj, $p_description, $p_runs_on = null)
    {
        if (is_numeric($p_id) && $p_id > 0) {
            $l_data_res = $this->get_data($p_id);
            $l_data = $l_data_res->get_row();

            $l_sql = 'UPDATE isys_catg_virtual_machine_list
                SET isys_catg_virtual_machine_list__isys_connection__id = ' . $this->convert_sql_id($this->handle_connection($p_id, $p_connected_obj)) . ',
                isys_catg_virtual_machine_list__status = ' . $this->convert_sql_int($p_status) . ',
                isys_catg_virtual_machine_list__vm = 2,
                isys_catg_virtual_machine_list__description = ' . $this->convert_sql_text($p_description);

            if (!is_null($p_runs_on) && !empty($p_runs_on)) {
                $l_sql .= ", isys_catg_virtual_machine_list__primary = " . $this->convert_sql_id($p_runs_on);
            }

            $l_sql .= ' WHERE isys_catg_virtual_machine_list__id = ' . $this->convert_sql_id($p_id) . ';';

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL .= $l_sql;

                if ($this->apply_update()) {
                    $l_data_res = $this->get_data($p_id);
                    $l_data_puffer = $l_data_res->get_row();

                    if ($l_data_puffer != false) {
                        $l_data = $l_data_puffer;
                    }

                    // Create implicit relation.
                    $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                    $l_relation_dao->handle_relation(
                        $p_id,
                        "isys_catg_virtual_machine_list",
                        defined_or_default('C__RELATION_TYPE__VIRTUAL_MACHINE'),
                        $l_data["isys_catg_virtual_machine_list__isys_catg_relation_list__id"],
                        $p_connected_obj,
                        $l_data["isys_catg_virtual_machine_list__isys_obj__id"]
                    );

                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
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
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_database
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = null;
        $l_current_objects = [];

        $l_tmpDao = $this->get_data(null, $p_object_id);

        while ($l_row = $l_tmpDao->get_row()) {
            if (!in_array($l_row["isys_obj__id"], $p_objects)) {
                $this->save($l_row["isys_catg_virtual_machine_list__id"], C__RECORD_STATUS__NORMAL, null, null);

                if ($l_row["isys_catg_relation_list__isys_obj__id"] > 0) {
                    $this->delete_object($l_row["isys_catg_relation_list__isys_obj__id"]);
                }
            }
            $l_current_objects[$l_row['isys_obj__id']] = true;
        }

        foreach ($p_objects as $l_object_id) {
            if (is_numeric($l_object_id)) {
                if ($_GET[C__CMDB__GET__OBJECTTYPE] != defined_or_default('C__OBJTYPE__CLUSTER')) {
                    $l_runs_on = $_GET[C__CMDB__GET__OBJECT];
                } else {
                    $l_runs_on = "";
                }

                if (!isset($l_current_objects[$l_object_id]) || !$l_current_objects[$l_object_id]) {
                    $l_query = 'SELECT isys_catg_virtual_machine_list__id FROM isys_catg_virtual_machine_list WHERE isys_catg_virtual_machine_list__isys_obj__id = ' .
                        $this->convert_sql_id($l_object_id) . ' ORDER BY isys_catg_virtual_machine_list__id DESC';
                    $l_res = $this->retrieve($l_query);
                    if ($l_res->num_rows() >= 1) {
                        $l_id = $l_res->get_row_value('isys_catg_virtual_machine_list__id');
                        $this->save($l_id, C__RECORD_STATUS__NORMAL, $_GET[C__CMDB__GET__OBJECT], '', $l_runs_on);
                        if ($l_res->num_rows() > 1) {
                            // Delete older entries
                            while ($l_row = $l_res->get_row()) {
                                $this->delete_entry(current($l_row), 'isys_catg_virtual_machine_list');
                            }
                        }
                    } else {
                        $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $l_object_id, "", $l_runs_on);
                    }
                }
            }
        }

        return $l_id;
    }

    /**
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(guest.isys_obj__id) AS count FROM isys_catg_virtual_machine_list " .
            "INNER JOIN isys_obj guest ON isys_catg_virtual_machine_list__isys_obj__id = guest.isys_obj__id " .
            "INNER JOIN isys_connection ON isys_catg_virtual_machine_list__isys_connection__id = isys_connection__id " .
            "INNER JOIN isys_obj me ON me.isys_obj__id = isys_connection__isys_obj__id " . "WHERE TRUE AND isys_catg_virtual_machine_list__vm = '2' " .
            "AND isys_catg_virtual_machine_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " " . "AND guest.isys_obj__status = " .
            $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($l_obj_id)) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        }

        return (int)$this->retrieve($l_sql . ';')
            ->get_row_value('count');
    }

    /**
     * Return Category Data
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = "SELECT guest.*, isys_catg_virtual_machine_list.*, isys_connection.*, isys_catg_relation_list__isys_obj__id " . "FROM isys_catg_virtual_machine_list " .
            "INNER JOIN isys_obj guest ON isys_catg_virtual_machine_list__isys_obj__id = guest.isys_obj__id " .
            "INNER JOIN isys_connection ON isys_catg_virtual_machine_list__isys_connection__id = isys_connection__id " .
            "INNER JOIN isys_obj me ON me.isys_obj__id = isys_connection__isys_obj__id " .
            "LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_virtual_machine_list__isys_catg_relation_list__id " .
            "WHERE TRUE AND isys_catg_virtual_machine_list__vm = '2' " . $p_condition . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id) . " ";
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_virtual_machine_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_virtual_machine_list__status = " . $this->convert_sql_int($p_status);
        }

        $l_sql .= " AND guest.isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if ($p_obj_id !== null) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GUEST_SYSTEMS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Guest systems'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_virtual_machine_list__isys_obj__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__VIRTUAL_MACHINE'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_guest_systems',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_guest_systems']),
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_virtual_machine_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_machine_list__isys_obj__id
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_machine_list__isys_connection__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_virtual_machine_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_virtual_machine_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_virtual_machine_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__GUEST_SYSTEM_CONNECTED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        // @todo Property Callback for multiedit (in future).
                        'relationFilter' => "C__RELATION_TYPE__SOFTWARE;C__RELATION_TYPE__CLUSTER_SERVICE",
                        'typeFiler'      => "C__OBJTYPE__VIRTUAL_CLIENT;C__OBJTYPE__SERVER;C__OBJTYPE__VIRTUAL_CLIENT;C__OBJTYPE__VIRTUAL_SERVER;C__OBJTYPE__HOST"
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'guest_systems'
                    ]
                ]
            ]),
            'hostname'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__HOSTNAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostname'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_machine_list__isys_obj__id',
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
                        'guest_system_property_hostname'
                    ]
                ]
            ]),
            'runs_on'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Runs on'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_machine_list__primary',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ])
        ];
    }

    /**
     * Rank records method
     *
     * @param array  $p_objects
     * @param int    $p_direction
     * @param string $p_table
     *
     * @return bool
     */
    public function rank_records($p_cat_ids, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($p_cat_ids)) {
                    $l_res = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT]);
                    $l_data = $l_before = $l_changed = [];
                    while ($l_row = $l_res->get_row()) {
                        $l_cat_id = $l_row['isys_catg_virtual_machine_list__id'];
                        if (in_array($l_cat_id, $p_cat_ids)) {
                            $l_data[$l_cat_id] = $l_row;
                        }
                        $l_before[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
                    }

                    $l_changed = $l_before;

                    /**
                     * @var $l_dao_rel isys_cmdb_dao_category_g_relation
                     */
                    $l_dao_rel = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
                    foreach ($p_cat_ids as $l_cat_id) {
                        $l_sql = 'UPDATE isys_catg_virtual_machine_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_machine_list__isys_connection__id
                            SET isys_connection__isys_obj__id = NULL,
                            isys_catg_virtual_machine_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
                            isys_catg_virtual_machine_list__vm = 2,
                            isys_catg_virtual_machine_list__primary = NULL
                            WHERE isys_catg_virtual_machine_list__id = ' . $this->convert_sql_id($l_cat_id) . ';';

                        if ($this->update($l_sql) && $this->apply_update()) {
                            $this->m_strLogbookSQL .= $l_sql;

                            $l_dao_rel->handle_relation(
                                $l_cat_id,
                                'isys_catg_virtual_machine_list',
                                defined_or_default('C__RELATION_TYPE__VIRTUAL_MACHINE'),
                                $l_data[$l_cat_id]['isys_catg_virtual_machine_list__isys_catg_relation_list__id']
                            );
                        }
                        unset($l_changed[$l_data[$l_cat_id]['isys_obj__id']]);
                    }
                    $l_changes['isys_cmdb_dao_category_g_guest_systems::connected_object'] = [
                        'from' => implode(', ', $l_before),
                        'to'   => implode(', ', $l_changed)
                    ];

                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            "C__LOGBOOK_EVENT__CATEGORY_CHANGED",
                            $this->m_strLogbookSQL,
                            $_GET[C__CMDB__GET__OBJECT],
                            $this->get_objTypeID($_GET[C__CMDB__GET__OBJECT]),
                            'LC__CMDB__CATG__GUEST_SYSTEMS',
                            serialize($l_changes)
                        );
                }
                break;
        }

        return true;
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if (!isset($p_category_data['properties']['connected_object'][C__DATA__VALUE])) {
                return false;
            }
            $l_sql = 'SELECT isys_catg_virtual_machine_list__id FROM isys_catg_virtual_machine_list
						WHERE isys_catg_virtual_machine_list__isys_obj__id = ' . $this->convert_sql_id($p_category_data['properties']['connected_object'][C__DATA__VALUE]);
            $l_res = $this->retrieve($l_sql);
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($l_res->num_rows() > 0) {
                        $p_category_data['data_id'] = $l_res->get_row_value('isys_catg_virtual_machine_list__id');
                        $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_object_id, null, null);

                        return $p_category_data['data_id'];
                    } elseif ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            null
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($l_res->num_rows() == 0 && $p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            null
                        );
                    } elseif ($p_category_data['data_id'] > 0) {
                        $p_category_data['data_id'] = $l_res->get_row_value('isys_catg_virtual_machine_list__id');
                        $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_object_id, null, null);

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}
