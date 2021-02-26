<?php

/**
 * i-doit
 *
 * DAO: global category for Remote Management Controller
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_rm_controller extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'rm_controller';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_rm_controller_list__isys_obj__id';

    /**
     * Dynamic property handling for connected object
     *
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_general
     */
    public function dynamic_property_callback_connected_object($p_row)
    {
        global $g_comp_database;

        /**
         * @var $l_dao isys_cmdb_dao_category_g_rm_controller
         */
        $l_dao = isys_cmdb_dao_category_g_rm_controller::instance($g_comp_database);
        $l_quickinfo = new isys_ajax_handler_quick_info();

        if (!isset($p_row['isys_connection__isys_obj__id'])) {
            $p_row['isys_connection__isys_obj__id'] = $l_dao->get_data(null, $p_row['isys_obj__id'])
                ->get_row_value('isys_connection__isys_obj__id');
        }
        $l_title = $l_dao->get_obj_name_by_id_as_string($p_row['isys_connection__isys_obj__id']);

        return ($l_title !== '') ? $l_quickinfo->get_quick_info($p_row['isys_connection__isys_obj__id'], $l_title, C__LINK__OBJECT) : "";
    }

    /**
     * Dynamic property handling for getting the primary access url
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_remote_url($p_row)
    {
        global $g_comp_database;

        /**
         * @var $l_dao isys_cmdb_dao_category_g_access
         */
        $l_dao = isys_cmdb_dao_category_g_access::instance($g_comp_database);
        if (!isset($p_row['isys_connection__isys_obj__id'])) {
            $l_dao_rm = isys_cmdb_dao_category_g_rm_controller::instance($g_comp_database);;
            $p_row['isys_connection__isys_obj__id'] = $l_dao_rm->get_data(null, $p_row['isys_obj__id'])
                ->get_row_value('isys_connection__isys_obj__id');
        }
        $l_res = $l_dao->get_primary_element($p_row['isys_connection__isys_obj__id']);

        if ($l_res->num_rows() > 0) {
            $l_data = $l_res->get_row();

            return isys_helper_link::handle_url_variables($l_data['isys_catg_access_list__url'], $p_row['isys_obj__id']);
        } else {
            return null;
        }
    }

    /**
     * @param type $p_cat_level
     * @param type $p_intOldRecStatus
     *
     * @return bool|int
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->get_row();
        $p_intOldRecStatus = $l_catdata["isys_catg_rm_controller_list__status"];
        $l_list_id = $l_catdata["isys_catg_rm_controller_list__id"];

        $l_status = C__RECORD_STATUS__NORMAL;

        if (empty($l_list_id)) {
            $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], $l_status, $_POST['C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_id) {
                $p_cat_level = 1;

                return $l_id;
            }
        } else {
            if ($this->save($l_catdata['isys_catg_rm_controller_list__id'], $l_status, $_POST['C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()])) {
                $this->m_strLogbookSQL = $this->get_last_query();

                return true;
            }
        }

        return false;
    }

    /**
     * @param int    $p_object_id
     * @param int    $p_status
     * @param null   $p_assigned_object
     * @param string $p_description
     *
     * @return bool|int
     * @throws isys_exception_dao
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_object_id, $p_status = C__RECORD_STATUS__NORMAL, $p_assigned_object = null, $p_description = '')
    {
        $l_connection = isys_cmdb_dao_connection::instance($this->m_db);
        $l_last_id = false;

        $l_sql = "INSERT INTO isys_catg_rm_controller_list (
			isys_catg_rm_controller_list__isys_obj__id,
			isys_catg_rm_controller_list__status,
			isys_catg_rm_controller_list__isys_connection__id,
			isys_catg_rm_controller_list__description)
			VALUES(
			" . $this->convert_sql_id($p_object_id) . ",
			" . $this->convert_sql_int($p_status) . ",
			" . $this->convert_sql_id($l_connection->add_connection($p_assigned_object)) . ",
			" . $this->convert_sql_text($p_description) . ");";

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $l_relation_dao->handle_relation($l_last_id, "isys_catg_rm_controller_list", defined_or_default('C__RELATION_TYPE__RM_CONTROLLER'), null, $p_object_id, $p_assigned_object);

        }

        return $l_last_id;
    }

    /**
     * @param int       $p_cat_level
     * @param array|int $p_status
     * @param null      $p_assigned_object
     * @param string    $p_description
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_cat_level, $p_status = C__RECORD_STATUS__NORMAL, $p_assigned_object = null, $p_description = '')
    {
        $l_old_data = $this->get_data($p_cat_level)
            ->get_row();

        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection_id = $l_connection->update_connection($l_old_data["isys_catg_rm_controller_list__isys_connection__id"], $p_assigned_object);

        $l_sql = "UPDATE isys_catg_rm_controller_list SET
			isys_catg_rm_controller_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_rm_controller_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_rm_controller_list__isys_connection__id = " . $this->convert_sql_id($l_connection_id) . "
			WHERE isys_catg_rm_controller_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $l_data = $this->get_data($p_cat_level)
                ->__to_array();

            $l_relation_dao->handle_relation($p_cat_level, "isys_catg_rm_controller_list", defined_or_default('C__RELATION_TYPE__RM_CONTROLLER'),
                $l_data["isys_catg_rm_controller_list__isys_catg_relation_list__id"], $l_data["isys_catg_rm_controller_list__isys_obj__id"], $p_assigned_object);

            return true;
        }

        return false;
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
            'remote_url'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary access url'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_access_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(isys_cmdb_dao_category_g_access::build_formatted_url_query() . '
                        	LEFT JOIN isys_connection ON isys_connection__isys_obj__id = acc.isys_catg_access_list__isys_obj__id
                        	LEFT JOIN isys_catg_rm_controller_list ON isys_catg_rm_controller_list__isys_connection__id = isys_connection__id', 'isys_catg_rm_controller_list',
                        '', 'isys_catg_rm_controller_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([' AND acc.isys_catg_access_list__primary = 1']))
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__RM_CONTROLLER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Remote Management Controller'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_rm_controller_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__RM_CONTROLLER'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_rm_controller',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_rm_controller']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_rm_controller_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_rm_controller_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id', 'isys_catg_rm_controller_list', 'isys_catg_rm_controller_list__id',
                        'isys_catg_rm_controller_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_rm_controller_list', 'LEFT', 'isys_catg_rm_controller_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_catg_rm_controller_list__isys_connection__id',
                            'isys_connection__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__IP',
                        'multiselection'                            => false
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__SEARCH     => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_rm_controller_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__RM_CONTROLLER', 'C__CATG__RM_CONTROLLER')
                ],
            ])
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
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                return $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]);
            } elseif ($p_status == isys_import_handler_cmdb::C__UPDATE) {
                if ($p_category_data['data_id'] === null) {
                    $l_sql = 'SELECT isys_catg_rm_controller_list__id FROM isys_catg_rm_controller_list
					WHERE isys_catg_rm_controller_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);
                    $l_res = $this->retrieve($l_sql . ';');
                    if ($l_res->num_rows() > 0) {
                        $p_category_data['data_id'] = $l_res->get_row_value('isys_catg_rm_controller_list__id');
                    } else {
                        return $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]);
                    }
                }

                $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]);

                return $p_category_data['data_id'];
            }
        }

        return false;
    }
}