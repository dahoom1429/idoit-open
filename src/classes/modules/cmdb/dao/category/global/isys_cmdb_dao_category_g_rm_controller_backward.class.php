<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for Remote Management Controller (Backward)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_rm_controller_backward extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'rm_controller_backward';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_rm_controller_list__isys_obj__id';

    /**
     * Flag to for multivalued category
     *
     * @var bool
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
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Category's table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_rm_controller_list';

    /**
     * Wrapper to retrieve all assigned objects
     *
     * @param $p_obj_id
     *
     * @return isys_component_dao_result
     */
    public function get_assigned_objects($p_obj_id)
    {
        return $this->get_data(null, $p_obj_id);
    }

    /**
     * Overwrite save_element which has no function
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  null
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        return null;
    }

    /**
     * Save global category remote management controller element.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_new_id
     *
     * @return  null
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_con_arr = $p_objects;

        $l_assigned_objects = [];
        $l_assigned_objects_res = $this->get_assigned_objects($p_object_id);

        if ($l_assigned_objects_res->num_rows() > 0) {
            while ($l_row = $l_assigned_objects_res->get_row()) {
                $l_assigned_objects[$l_row['isys_obj__id']] = $l_row['isys_catg_rm_controller_list__id'];
            }
        }

        if (is_array($l_con_arr)) {
            foreach ($l_con_arr as $l_val) {
                if (is_numeric($l_val) && $l_val != "on") {
                    if (!isset($l_assigned_objects[$l_val])) {
                        //unset($l_assigned_objects[$l_val]);
                        $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_val);
                    } else {
                        unset($l_assigned_objects[$l_val]);
                    }
                }
            }
        }

        if (count($l_assigned_objects) > 0) {
            foreach ($l_assigned_objects as $l_obj_id => $l_entry_id) {
                $this->delete_entry($l_entry_id, 'isys_catg_rm_controller_list');
            }
        }

        return null;
    }

    /**
     * Create method.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     *
     * @return  mixed
     */
    public function create($p_objID, $p_newRecStatus, $p_connectedObjID, $p_description = null)
    {
        /**
         * @var $l_dao isys_cmdb_dao_category_g_rm_controller
         */
        $l_dao = isys_cmdb_dao_category_g_rm_controller::instance($this->m_db);

        return $l_dao->create($p_connectedObjID, $p_newRecStatus, $p_objID, $p_description);
    }

    /**
     * Count method
     *
     * @param int|null $p_obj_id
     *
     * @return int
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        $l_sql = 'SELECT COUNT(isys_catg_rm_controller_list__id) AS count FROM isys_catg_rm_controller_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_rm_controller_list__isys_connection__id
			WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        return (int)$this->retrieve($l_sql)
            ->get_row_value('count');
    }

    /**
     * Retrieves data which devices are connected to the remote management controller
     *
     * @param null   $p_catg_list_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT isys_catg_rm_controller_list__id, isys_obj__id, isys_obj__title, isys_obj__isys_obj_type__id, isys_obj__sysid, isys_catg_rm_controller_list__isys_obj__id, isys_connection__isys_obj__id FROM isys_catg_rm_controller_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_rm_controller_list__isys_connection__id
			INNER JOIN isys_obj ON isys_catg_rm_controller_list__isys_obj__id = isys_obj__id
			WHERE TRUE ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= ' ' . $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_rm_controller_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_rm_controller_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Helper method to build the object condition
     *
     * @param int|null $p_obj_id
     *
     * @return string
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                return ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                return ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return '';
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__RM_CONTROLLER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Remote Management Controller'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_rm_controller_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_rm_controller_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_rm_controller_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_rm_controller_list__isys_obj__id',
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
                            'isys_catg_rm_controller_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_rm_controller_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_rm_controller_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__IP',
                        'multiselection'                            => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__SEARCH     => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
        ];
    }

    /**
     * Sync method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_connected_object = $this->convert_sql_id($p_category_data['properties']['connected_object'][C__DATA__VALUE]);

            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                return $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_connected_object, $p_category_data['properties']['description'][C__DATA__VALUE]);
            } elseif ($p_status == isys_import_handler_cmdb::C__UPDATE) {
                if ($p_category_data['data_id'] === null && $l_connected_object) {
                    $l_sql = 'SELECT isys_catg_rm_controller_list__id FROM isys_catg_rm_controller_list
					WHERE isys_catg_rm_controller_list__isys_obj__id = ' . $this->convert_sql_id($l_connected_object);
                    $l_res = $this->retrieve($l_sql . ';');
                    if ($l_res->num_rows()) {
                        $p_category_data['data_id'] = $l_res->get_row_value('isys_catg_rm_controller_list__id');
                    } else {
                        return $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_connected_object, $p_category_data['properties']['description'][C__DATA__VALUE]);
                    }
                }

                return $p_category_data['data_id'];
            }
        }

        return false;
    }
}
