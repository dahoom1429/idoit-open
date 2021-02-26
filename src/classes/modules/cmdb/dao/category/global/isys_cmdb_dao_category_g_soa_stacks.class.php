<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_soa_stacks extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'soa_stacks';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method for the soa components dialog-list field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_soa_components(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        $l_dao_app = new isys_cmdb_dao_category_g_application($this->get_database_component());
        $l_dao_rel = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_res = $l_dao_app->get_data(null, $l_obj_id);
        $l_cat_id = $p_request->get_category_data_id();
        while ($l_row = $l_res->get_row()) {
            $l_data = $l_dao_rel->get_data($l_row['isys_catg_application_list__isys_catg_relation_list__id'])
                ->get_row();

            if ($l_cat_id > 0) {
                $l_res_assigned = ($this->get_assigned_object($l_cat_id, $l_data["isys_catg_relation_list__isys_obj__id"]));
            }

            if ($l_res_assigned) {
                $l_selected = true;
            } else {
                $l_selected = false;
            }

            $l_return[] = [
                "val" => $l_data['isys_obj__title'],
                "hid" => 0,
                "sel" => $l_selected,
                "id"  => $l_data['isys_catg_relation_list__isys_obj__id']
            ];
        }

        return $l_return;
    }

    /**
     * Return Category Data.
     *
     * @param   itneger $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT *, isys_connection__isys_obj__id AS import1, isys_connection__isys_obj__id AS import2, isys_connection__isys_obj__id AS import3 " .
            "FROM isys_catg_soa_stacks_list " . "INNER JOIN isys_obj ON isys_catg_soa_stacks_list__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_connection ON isys_connection__id = isys_catg_soa_stacks_list__isys_connection__id " .
            "LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_soa_stacks_list__isys_catg_relation_list__id " . "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= "AND (isys_catg_soa_stacks_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND (isys_catg_soa_stacks_list__status = " . $this->convert_sql_int($p_status) . ") ";
        }

        $l_sql .= "ORDER BY isys_obj__isys_obj_type__id ASC;";

        return $this->retrieve($l_sql);
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_soa_stack_object($p_row)
    {
        if (!empty($p_row['isys_catg_soa_stacks_list__id'])) {
            $l_dao = isys_cmdb_dao_category_g_soa_stacks::instance(isys_application::instance()->database);
            $l_return = $l_dao->retrieve('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') AS val
                            FROM isys_catg_soa_stacks_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_soa_stacks_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                            WHERE isys_catg_soa_stacks_list__id = ' . $l_dao->convert_sql_id($p_row['isys_catg_soa_stacks_list__id']))
                ->get_row_value('val');
            if (!empty($l_return)) {
                return $l_return;
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param $p_row
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_soa_stack_component($p_row)
    {
        if (!empty($p_row['isys_catg_soa_stacks_list__id'])) {
            $l_dao = isys_cmdb_dao_category_g_soa_stacks::instance(isys_application::instance()->database);
            $l_return = $l_dao->retrieve('SELECT GROUP_CONCAT(CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') SEPARATOR \', \') AS val
                            FROM isys_catg_soa_stacks_list
                            INNER JOIN isys_connection con1 ON con1.isys_connection__id = isys_catg_soa_stacks_list__isys_connection__id
                            INNER JOIN isys_cats_group_list ON isys_cats_group_list__isys_obj__id = con1.isys_connection__isys_obj__id
                            INNER JOIN isys_connection con2 ON con2.isys_connection__id = isys_cats_group_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_connection__isys_obj__id
                            WHERE isys_catg_soa_stacks_list__id = ' . $l_dao->convert_sql_id($p_row['isys_catg_soa_stacks_list__id']) . '
                            GROUP BY isys_catg_soa_stacks_list__isys_obj__id')
                ->get_row_value('val');
            if (!empty($l_return)) {
                return $l_return;
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_soa_stack_object' => new DynamicProperty(
                'LC__CMDB__CATG__SOA_STACKS',
                'isys_catg_soa_stacks_list__id',
                'isys_catg_soa_stacks_list',
                [
                    $this,
                    'dynamic_property_callback_soa_stack_object'
                ]
            ),
            '_soa_stack_components' => new DynamicProperty(
                'LC__CMDB__CATG__SOA_COMPONENTS',
                'isys_catg_soa_stacks_list__id',
                'isys_catg_soa_stacks_list',
                [
                    $this,
                    'dynamic_property_callback_soa_stack_component'
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
            'title'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_soa_stacks_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_soa_stacks_list__title FROM isys_catg_soa_stacks_list',
                        'isys_catg_soa_stacks_list',
                        'isys_catg_soa_stacks_list__id',
                        'isys_catg_soa_stacks_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_soa_stacks_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SOA_STACKS__TITLE'
                ]
            ]),
            'soa_stack_object'      => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SOA_STACKS',
                    C__PROPERTY__INFO__DESCRIPTION => 'SOA-Stack'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_connection__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_soa_stacks_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_soa_stacks_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_soa_stacks_list',
                        'isys_catg_soa_stacks_list__id',
                        'isys_catg_soa_stacks_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_soa_stacks_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_soa_stacks_list',
                            'LEFT',
                            'isys_catg_soa_stacks_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_soa_stacks_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                    //						C__PROPERTY__DATA__RELATION_TYPE => C__RELATION_TYPE__SOA_STACKS,
                    //						C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(array('isys_cmdb_dao_category_g_soa_stacks', 'callback_property_relation_handler'), array('isys_cmdb_dao_category_g_soa_stacks')),
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'soa_stack_object'
                    ]
                ]
            ]),
            'soa_stack_components'  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_list(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SOA_COMPONENTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SOA_COMPONENTS'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_soa_stacks_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_soa_stacks_list
                            INNER JOIN isys_connection con1 ON con1.isys_connection__id = isys_catg_soa_stacks_list__isys_connection__id
                            INNER JOIN isys_cats_group_list ON isys_cats_group_list__isys_obj__id = con1.isys_connection__isys_obj__id
                            INNER JOIN isys_connection con2 ON con2.isys_connection__id = isys_cats_group_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_connection__isys_obj__id',
                        'isys_catg_soa_stacks_list',
                        'isys_catg_soa_stacks_list__id',
                        'isys_catg_soa_stacks_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_soa_stacks_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_soa_stacks_list',
                            'LEFT',
                            'isys_catg_soa_stacks_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_soa_stacks_list__isys_connection__id',
                            'isys_connection__id',
                            '',
                            'con1',
                            'con1'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_group_list',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_cats_group_list__isys_obj__id',
                            'con1'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_cats_group_list__isys_connection__id',
                            'isys_connection__id',
                            '',
                            'con2',
                            'con2'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id', 'con2')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__SOA_STACKS__COMPONENTS_LIST',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_soa_stacks',
                            'callback_property_soa_components'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'soa_stack_components'
                    ]
                ]
            ]),
            'soa_stack_it_services' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IT_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Service'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_connection__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_soa_stacks_list
                            INNER JOIN isys_connection con1 ON con1.isys_connection__id = isys_catg_soa_stacks_list__isys_connection__id
                            INNER JOIN isys_connection con2 ON con2.isys_connection__isys_obj__id = con1.isys_connection__isys_obj__id AND con2.isys_connection__id != con1.isys_connection__id
                            INNER JOIN isys_catg_its_components_list ON isys_catg_its_components_list__isys_connection__id = con2.isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_its_components_list__isys_obj__id',
                        'isys_catg_soa_stacks_list',
                        'isys_catg_soa_stacks_list__id',
                        'isys_catg_soa_stacks_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_soa_stacks_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__SOA_STACKS__IT_SERVICE',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        'catFilter'      => 'C__CATG__SERVICE'
                        // @todo Property Callback for multiedit (in future).
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true,
                    C__PROPERTY__PROVIDES__REPORT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'soa_stack_it_services'
                    ]
                ]
            ]),
            'description'           => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_soa_stacks_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_soa_stacks_list__description FROM isys_catg_soa_stacks_list',
                        'isys_catg_soa_stacks_list',
                        'isys_catg_soa_stacks_list__id',
                        'isys_catg_soa_stacks_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_soa_stacks_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SOA_STACKS', 'C__CATG__SOA_STACKS'),
                ]
            ])
        ];
    }

    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__ARCHIVE:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;
            case C__NAVMODE__DELETE:
                $l_status = C__RECORD_STATUS__DELETED;
                break;

            case C__NAVMODE__RECYCLE:
                if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__ARCHIVED) {
                    $l_status = C__RECORD_STATUS__NORMAL;
                } elseif (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__DELETED) {
                    $l_status = C__RECORD_STATUS__ARCHIVED;
                }
                break;

            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($p_objects)) {
                    foreach ($p_objects as $l_cat_id) {
                        $this->delete_soa_stack($l_cat_id);
                    }
                }

                return true;
                break;
        }

        foreach ($p_objects as $l_cat_id) {
            $this->set_status($l_cat_id, $l_status);
        }

        return true;
    }

    /**
     * Method for synchronization.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     * @see     isys_cmdb_dao_category::sync()
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            $l_dao_app = new isys_cmdb_dao_category_g_application($this->get_database_component());
            $l_ss_components = $this->get_property('soa_stack_components');
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $l_ss_components,
                        $this->get_property('soa_stack_it_services'),
                        $this->get_property('description')
                    );
                    if ($p_category_data['data_id']) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $l_ss_components,
                        $this->get_property('soa_stack_it_services'),
                        $this->get_property('description')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Method for saving new elements.
     *
     * @param   integer & $p_cat_level
     * @param   integer & $p_status
     * @param   boolean $p_create
     *
     * @return  mixed  Integer with the last inserted ID, boolean (false) on failure.
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        $l_catdata = $this->get_general_data();

        if ($p_create && empty($l_catdata)) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATG__SOA_STACKS__TITLE"],
                $_POST["C__CATG__SOA_STACKS__COMPONENTS_LIST__selected_values"],
                $_POST["C__CATG__SOA_STACKS__IT_SERVICE__HIDDEN"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id > 0) {
                $p_cat_level = -1;

                return $l_id;
            }
        } else {
            $l_saved = $this->save(
                $l_catdata["isys_catg_soa_stacks_list__id"],
                $l_catdata["isys_catg_soa_stacks_list__status"],
                $_POST["C__CATG__SOA_STACKS__TITLE"],
                $_POST["C__CATG__SOA_STACKS__COMPONENTS_LIST__selected_values"],
                $_POST["C__CATG__SOA_STACKS__IT_SERVICE__HIDDEN"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_saved) {
                return null;
            }
        }

        return false;
    }

    /**
     * Add new SOA Stack.
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     * @param   string  $p_title
     * @param   mixed   $p_connected_components
     * @param   mixed   $p_connected_it_services
     * @param   string  $p_description
     *
     * @return  mixed  Integer of the last inserted ID on success, boolean (false) on failure.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_object_id, $p_status, $p_title, $p_connected_components, $p_connected_it_services, $p_description)
    {
        $l_dao_con = new isys_cmdb_dao_connection($this->m_db);
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

        if (!is_array($p_connected_components)) {
            $p_connected_components = explode(',', $p_connected_components);
        }

        if (!is_array($p_connected_it_services)) {
            if (strstr($p_connected_it_services, '[') && strstr($p_connected_it_services, ']')) {
                // Assume we got a JSON string.
                $p_connected_it_services = isys_format_json::decode($p_connected_it_services);
            } else {
                // Assume we got a comma-separated list.
                $p_connected_it_services = explode(',', $p_connected_it_services);
            }
        }

        if (defined('C__OBJTYPE__SOA_STACK')) {
            // CREATE NEW Object of TYPE C__OBJTYPE__SOA_STACK.
            if (!($l_soa_stack = $this->retrieve("SELECT * FROM isys_obj WHERE isys_obj__isys_obj_type__id = '" . C__OBJTYPE__SOA_STACK . "' AND isys_obj__title LIKE " .
                $this->convert_sql_text($p_title))
                ->get_row())) {
                $l_obj_id = $this->insert_new_obj(C__OBJTYPE__SOA_STACK, false, $p_title, null, C__RECORD_STATUS__NORMAL);
                $l_con_id = $l_dao_con->add_connection($l_obj_id);
            } else {
                $l_obj_id = $l_soa_stack['isys_obj__id'];
                $l_con_id = $l_dao_con->add_connection($l_obj_id);
            }
        }

        $l_sql = 'INSERT INTO isys_catg_soa_stacks_list
            SET isys_catg_soa_stacks_list__status = ' . $this->convert_sql_int($p_status) . ',
            isys_catg_soa_stacks_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_soa_stacks_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
            isys_catg_soa_stacks_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_catg_soa_stacks_list__isys_connection__id = ' . $this->convert_sql_id($l_con_id) . ';';

        if ($this->update($l_sql)) {
            if ($this->apply_update()) {
                $this->m_strLogbookSQL = $l_sql;

                $l_last_id = $this->get_last_insert_id();

                $l_dao_relation->handle_relation($l_last_id, "isys_catg_soa_stacks_list", defined_or_default('C__RELATION_TYPE__SOA_STACKS'), null, $p_object_id, $l_obj_id);

                $l_catdata = $this->get_data($l_last_id)
                    ->get_row();

                if (count($p_connected_components) > 0) {
                    foreach ($p_connected_components as $l_connected_obj_id) {
                        if ($l_connected_obj_id > 0) {
                            $this->attach_components($l_obj_id, $l_connected_obj_id);
                        }
                    }
                }

                if (is_countable($p_connected_it_services) && count($p_connected_it_services) > 0) {
                    foreach ($p_connected_it_services as $l_connected_it_service_id) {
                        if ($l_connected_it_service_id > 0) {
                            $this->attach_it_service_component($l_obj_id, $l_connected_it_service_id);
                        }
                    }
                }

                return $l_last_id;
            }
        }

        return false;
    }

    /**
     * Updates an existing SOA Stack.
     *
     * @param   integer $p_id
     * @param   integer $p_status
     * @param   string  $p_title
     * @param   mixed   $p_connected_obj
     * @param   mixed   $p_connected_it_services
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save($p_id, $p_status, $p_title, $p_connected_obj, $p_connected_it_services, $p_description)
    {
        if (is_numeric($p_id)) {
            $l_catdata = $this->get_data($p_id)
                ->get_row();
            $l_exists = [];
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

            if (!is_array($p_connected_obj)) {
                $p_connected_obj = explode(",", $p_connected_obj);
            }

            if (!is_array($p_connected_it_services) && strstr($p_connected_it_services, '[')) {
                $p_connected_it_services = isys_format_json::decode($p_connected_it_services, true);
            }

            if (trim($p_title) != trim($l_catdata["isys_catg_soa_stacks_list__title"])) {
                $l_sql = 'UPDATE isys_obj
                    SET isys_obj__title = ' . $this->convert_sql_text($p_title) . '
                    WHERE isys_obj__id = ' . $this->convert_sql_id($l_catdata["isys_connection__isys_obj__id"]) . ';';
                $this->update($l_sql);
            }

            // DETACH / ATTACH COMPONENTS.
            $l_connected_components = $this->get_assigned_object($l_catdata["isys_catg_soa_stacks_list__id"]);

            while ($l_row = $l_connected_components->get_row()) {
                if (!in_array($l_row["isys_obj__id"], $p_connected_obj)) {
                    $this->detach_components($l_catdata["isys_connection__isys_obj__id"], $l_row["isys_connection__isys_obj__id"]);
                } else {
                    $l_exists[] = $l_row["isys_obj__id"];
                }
            }

            if (count($p_connected_obj) > 0) {
                foreach ($p_connected_obj as $l_obj_id) {
                    if (!in_array($l_obj_id, $l_exists) && $l_obj_id > 0) {
                        $this->attach_components($l_catdata["isys_connection__isys_obj__id"], $l_obj_id);
                    }
                }
            }

            $l_sql = 'UPDATE isys_catg_soa_stacks_list 
                SET isys_catg_soa_stacks_list__isys_connection__id = ' . $this->convert_sql_id($this->handle_connection($p_id, $l_catdata["isys_connection__isys_obj__id"])) . ',
                isys_catg_soa_stacks_list__status = ' . $this->convert_sql_int($p_status) . ',
                isys_catg_soa_stacks_list__title= ' . $this->convert_sql_text($p_title) . ',
                isys_catg_soa_stacks_list__description = ' . $this->convert_sql_text($p_description) . '
                WHERE isys_catg_soa_stacks_list__id = ' . $this->convert_sql_id($p_id) . ';';

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL = $l_sql;

                if ($this->apply_update()) {
                    $l_dao_relation->handle_relation(
                        $p_id,
                        "isys_catg_soa_stacks_list",
                        defined_or_default('C__RELATION_TYPE__SOA_STACKS'),
                        $l_catdata["isys_catg_soa_stacks_list__isys_catg_relation_list__id"],
                        $l_catdata["isys_catg_soa_stacks_list__isys_obj__id"],
                        $l_catdata["isys_connection__isys_obj__id"]
                    );
                    $l_catdata = $this->get_data($p_id)
                        ->get_row();

                    unset($l_exists);
                    $l_exists = [];

                    // DETACH / ATTACH IT SERVICES.
                    $l_connected_it_services = $this->get_assigned_it_services($l_catdata["isys_connection__isys_obj__id"]);

                    if ($l_connected_it_services->num_rows() > 0) {
                        while ($l_row = $l_connected_it_services->get_row()) {
                            if (!in_array($l_row["isys_obj__id"], $p_connected_it_services)) {
                                $this->detach_it_service_component($l_row["isys_catg_its_components_list__id"]);
                            } else {
                                $l_exists[] = $l_row["isys_obj__id"];
                            }
                        }
                    }

                    if ($p_connected_it_services[0] != "") {
                        foreach ($p_connected_it_services as $l_obj_id) {
                            if (!in_array($l_obj_id, $l_exists) && $l_obj_id > 0) {
                                $this->attach_it_service_component($l_catdata['isys_connection__isys_obj__id'], $l_obj_id);
                            }
                        }
                    }

                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Attaches components to SOA Stack.
     *
     * @param   integer $p_obj_id           Stack object
     * @param   integer $p_connected_obj_id Middleware component
     */
    public function attach_components($p_obj_id, $p_connected_obj_id)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_dao_group = new isys_cmdb_dao_category_s_group($this->get_database_component());
        $l_id = $l_dao_group->create($p_obj_id, C__RECORD_STATUS__NORMAL, $p_connected_obj_id, "");

        $l_dao_relation->create_relation("isys_cats_group_list", $l_id, $p_connected_obj_id, $p_obj_id, defined_or_default('C__RELATION_TYPE__SOA_COMPONENTS'));

        // @todo Beziehungen hinzufügen - Dafür neue Beziehungsart erstellen.
    }

    /**
     * Detaches components from SOA Stack.
     *
     * @param   integer $p_obj_id           Stack object
     * @param   integer $p_component_obj_id Middleware component
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function detach_components($p_obj_id, $p_component_obj_id)
    {
        $l_dao = new isys_cmdb_dao_category_s_group($this->get_database_component());
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_res = $l_dao->get_data(null, $p_obj_id, " AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_component_obj_id));

        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();
            $l_row_relation = $l_dao_relation->get_data($l_row["isys_cats_group_list__isys_catg_relation_list__id"])
                ->get_row();

            if ($this->delete_object($l_row_relation["isys_catg_relation_list__isys_obj__id"])) {
                $this->update("DELETE FROM isys_cats_group_list WHERE isys_cats_group_list__id = " . $this->convert_sql_id($l_row["isys_cats_group_list__id"]) . ";");

                return $this->apply_update();
            } else {
                throw new isys_exception_cmdb("Could not delete relation");
            }
        }
    }

    /**
     * Attaches SOA Stack relation to IT-Service as IT-Service component
     *
     * @param int $p_obj_id     Stack relation object
     * @param int $p_it_service IT-Service Object
     *
     * @return mixed var
     */
    public function attach_it_service_component($p_obj_id, $p_it_service)
    {
        $l_dao = new isys_cmdb_dao_category_g_it_service_components($this->get_database_component());
        $l_res = $l_dao->get_data(null, $p_it_service, " AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id));
        $l_row = $l_res->get_row();

        if (!$l_row) {
            return $l_dao->create($p_it_service, C__RECORD_STATUS__NORMAL, $p_obj_id, "");
        }
    }

    /**
     * Detaches SOA Stack relation from IT-Service
     *
     * @param int $p_cat_id Component category id
     *
     * @return bool
     */
    public function detach_it_service_component($p_cat_id)
    {
        $l_dao = new isys_cmdb_dao_category_g_it_service_components($this->get_database_component());
        $l_row = $l_dao->get_data($p_cat_id)
            ->get_row();

        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        return $l_dao_relation->delete_relation($l_row["isys_catg_its_components_list__isys_catg_relation_list__id"]);
    }

    /**
     * Gets assigned it services.
     *
     * @param   integer $p_obj_id Relation id
     *
     * @return  isys_component_dao_result|boolean
     */
    public function get_assigned_it_services($p_obj_id)
    {
        if ($p_obj_id > 0) {
            return isys_factory::get_instance('isys_cmdb_dao_category_g_it_service_components', $this->get_database_component())
                ->get_data(null, null, " AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id));
        } else {
            return false;
        }
    }

    /**
     * Gets assigned components.
     *
     * @param   integer $p_cat_id           category id
     * @param   integer $p_connected_obj_id connected object
     *
     * @return  isys_component_dao_result|boolean
     */
    public function get_assigned_object($p_cat_id = null, $p_connected_obj_id = null)
    {
        $l_sql = "SELECT comp.*, members.*, isys_catg_relation_list.*, master.isys_obj__title AS master_title, slave.isys_obj__title AS slave_title FROM isys_catg_soa_stacks_list
			LEFT JOIN isys_connection AS stack ON stack.isys_connection__id = isys_catg_soa_stacks_list__isys_connection__id
			LEFT JOIN isys_cats_group_list ON isys_cats_group_list__isys_obj__id = stack.isys_connection__isys_obj__id
			LEFT JOIN isys_connection AS comp ON isys_cats_group_list__isys_connection__id = comp.isys_connection__id
			LEFT JOIN isys_obj AS members ON members.isys_obj__id = comp.isys_connection__isys_obj__id
			LEFT JOIN isys_catg_relation_list ON comp.isys_connection__isys_obj__id = isys_catg_relation_list__isys_obj__id
			LEFT JOIN isys_obj AS master ON master.isys_obj__id = isys_catg_relation_list__isys_obj__id__master
			LEFT JOIN isys_obj AS slave ON slave.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
			WHERE TRUE ";

        if (!empty($p_cat_id)) {
            $l_sql .= "AND isys_catg_soa_stacks_list__id = " . $this->convert_sql_id($p_cat_id) . " ";
        }

        if (!empty($p_connected_obj_id)) {
            $l_sql .= "AND comp.isys_connection__isys_obj__id = " . $this->convert_sql_id($p_connected_obj_id);
        }

        $l_res = $this->retrieve($l_sql . ';');

        if (is_countable($l_res) && count($l_res)) {
            return $l_res;
        } else {
            return false;
        }
    }

    private function set_status($p_cat_id, $p_status)
    {
        $l_update = "UPDATE isys_catg_soa_stacks_list " . "SET isys_catg_soa_stacks_list__status = " . $p_status . " " . "WHERE isys_catg_soa_stacks_list__id = " .
            $this->convert_sql_id($p_cat_id);

        $this->update($l_update);

        return $this->apply_update();
    }

    private function delete_soa_stack($p_cat_id)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_catdata = $this->get_data($p_cat_id)
            ->get_row();

        if ($l_catdata === false) {
            return null; // Do nothing
        }

        $l_delete = "DELETE FROM isys_catg_soa_stacks_list WHERE isys_catg_soa_stacks_list__id = " . $this->convert_sql_id($l_catdata["isys_catg_soa_stacks_list__id"]);
        $l_delete_soa_hidden = "DELETE FROM isys_obj WHERE isys_obj__id = " . $this->convert_sql_id($l_catdata["isys_connection__isys_obj__id"]);
        $l_res = $l_dao_relation->get_data(null, $l_catdata["isys_connection__isys_obj__id"]);

        while ($l_row = $l_res->get_row()) {
            if ($l_row["isys_catg_relation_list__isys_obj__id"] > 0) {
                $l_delete_soa_hidden .= " OR isys_obj__id = " . $this->convert_sql_id($l_row["isys_catg_relation_list__isys_obj__id"]);
            }
        }

        $l_relation_object = $l_dao_relation->get_data($l_catdata["isys_catg_soa_stacks_list__isys_catg_relation_list__id"])
            ->get_row();
        $l_assigned_its = $this->get_assigned_it_services($l_relation_object["isys_catg_relation_list__isys_obj__id"]);
        if ($l_assigned_its->num_rows() > 0) {
            while ($l_row = $l_assigned_its->get_row()) {
                if ($l_row['isys_catg_its_components_list__id'] > 0) {
                    $this->detach_it_service_component($l_row["isys_catg_its_components_list__id"]);
                }
            }
        }

        $this->update($l_delete);
        $this->update($l_delete_soa_hidden);

        return $this->apply_update();
    }
}
