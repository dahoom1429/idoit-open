<?php

/**
 * i-doit
 *
 * DAO: specific category for database gateways.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_gateway extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_gateway';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'host';

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
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_database_gateway_list__isys_obj__id';

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
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_database_gateway_list " . "INNER JOIN isys_obj ON isys_cats_database_gateway_list__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_connection ON isys_connection__id = isys_cats_database_gateway_list__isys_connection__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND (isys_cats_database_gateway_list__id = " . (int)$p_cats_list_id . ")";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_cats_database_gateway_list__status = " . (int)$p_status . ")";
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
            'type'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_GATEWAY__GATEWAY_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Gateway type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_gateway_list__type',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_database_gateway_list__type FROM isys_cats_database_gateway_list',
                        'isys_cats_database_gateway_list', 'isys_cats_database_gateway_list__id', 'isys_cats_database_gateway_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_gateway_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__DATABASE_GATEWAY__GATEWAY_TYPE'
                ]
            ]),
            'host'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_GATEWAY__HOST',
                    C__PROPERTY__INFO__DESCRIPTION => 'Host'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_gateway_list__host',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_database_gateway_list__host FROM isys_cats_database_gateway_list',
                        'isys_cats_database_gateway_list', 'isys_cats_database_gateway_list__id', 'isys_cats_database_gateway_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_gateway_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__DATABASE_GATEWAY__HOST'
                ]
            ]),
            'port'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Port'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_gateway_list__port',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_database_gateway_list__port FROM isys_cats_database_gateway_list',
                        'isys_cats_database_gateway_list', 'isys_cats_database_gateway_list__id', 'isys_cats_database_gateway_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_gateway_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__DATABASE_GATEWAY__PORT'
                ]
            ]),
            'user'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_GATEWAY__USER',
                    C__PROPERTY__INFO__DESCRIPTION => 'User'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_gateway_list__user',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_database_gateway_list__user FROM isys_cats_database_gateway_list',
                        'isys_cats_database_gateway_list', 'isys_cats_database_gateway_list__id', 'isys_cats_database_gateway_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_gateway_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__DATABASE_GATEWAY__USER'
                ]
            ]),
            'target_schema' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA',
                    C__PROPERTY__INFO__DESCRIPTION => 'Target schema'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_database_gateway_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__DATABASE_GATEWAY'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_database_gateway',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_database_gateway',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_obj__title
                            FROM isys_cats_database_gateway_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_gateway_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id', 'isys_cats_database_gateway_list', 'isys_cats_database_gateway_list__id',
                        'isys_cats_database_gateway_list__isys_obj__id', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_gateway_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_database_gateway_list', 'LEFT', 'isys_cats_database_gateway_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_cats_database_gateway_list__isys_connection__id',
                            'isys_connection__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__DATABASE_GATEWAY__TARGET_SCHEMA',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_object_ng',
                        'catFilter'      => 'C__CATS__DATABASE_SCHEMA'
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
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_gateway_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_database_gateway_list__description FROM isys_cats_database_gateway_list',
                        'isys_cats_database_gateway_list', 'isys_cats_database_gateway_list__id', 'isys_cats_database_gateway_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_gateway_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__DATABASE_GATEWAY', 'C__CATS__DATABASE_GATEWAY')
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
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                if ($p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['host'][C__DATA__VALUE], $p_category_data['properties']['port'][C__DATA__VALUE],
                    $p_category_data['properties']['user'][C__DATA__VALUE], $p_category_data['properties']['target_schema'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE])) {
                    $l_indicator = true;
                }
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['host'][C__DATA__VALUE], $p_category_data['properties']['port'][C__DATA__VALUE],
                    $p_category_data['properties']['user'][C__DATA__VALUE], $p_category_data['properties']['target_schema'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]);
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save specific category monitor
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_database_gateway_list__status"];

        $l_list_id = $l_catdata["isys_cats_database_gateway_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, "", "", "", "", null);
        }

        if ($l_list_id) {
            $l_bRet = $this->save($l_list_id, C__RECORD_STATUS__NORMAL, $_POST['C__CATS__DATABASE_GATEWAY__GATEWAY_TYPE'], $_POST['C__CATS__DATABASE_GATEWAY__HOST'],
                $_POST['C__CATS__DATABASE_GATEWAY__PORT'], $_POST['C__CATS__DATABASE_GATEWAY__USER'], $_POST['C__CATS__DATABASE_GATEWAY__TARGET_SCHEMA__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_newRecStatus
     * @param String $p_display
     * @param int    $p_unitID
     * @param int    $p_typeID
     * @param int    $p_resolutionID
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_type, $p_host, $p_port, $p_user, $p_connectedObjID, $p_description)
    {

        $l_strSql = "UPDATE isys_cats_database_gateway_list " . "SET " . "isys_cats_database_gateway_list__isys_connection__id = " .
            $this->convert_sql_id($this->handle_connection($p_cat_level, $p_connectedObjID)) . ", " . "isys_cats_database_gateway_list__type = " .
            $this->convert_sql_text($p_type) . ", " . "isys_cats_database_gateway_list__host = " . $this->convert_sql_text($p_host) . ", " .
            "isys_cats_database_gateway_list__port = " . $this->convert_sql_text($p_port) . ", " . "isys_cats_database_gateway_list__user = " .
            $this->convert_sql_text($p_user) . ", " . "isys_cats_database_gateway_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " .
            "isys_cats_database_gateway_list__description = " . $this->convert_sql_text($p_description) . " " . "WHERE isys_cats_database_gateway_list__id = " .
            $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql) && $this->apply_update()) {

            /**
             * Handle relation
             */
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_data = $this->get_data($p_cat_level)
                ->__to_array();

            $l_relation_dao->handle_relation($p_cat_level, "isys_cats_database_gateway_list", defined_or_default('C__RELATION_TYPE__DATABASE_GATEWAY'),
                $l_data["isys_cats_database_gateway_list__isys_catg_relation_list__id"], $p_connectedObjID, $l_data["isys_cats_database_gateway_list__isys_obj__id"]);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry
     *
     * @param int    $p_objID
     * @param int    $p_newRecStatus
     * @param String $p_display
     * @param int    $p_unitID
     * @param int    $p_typeID
     * @param int    $p_resolutionID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_type = "", $p_host = "", $p_port = "", $p_user = "", $p_connectedObjID = null, $p_description = "")
    {

        $l_dao_conn = new isys_cmdb_dao_connection($this->m_db);
        $l_conn_id = $l_dao_conn->add_connection($p_connectedObjID);

        $l_strSql = "INSERT INTO isys_cats_database_gateway_list SET " . "isys_cats_database_gateway_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ", " .
            "isys_cats_database_gateway_list__isys_connection__id = " . $this->convert_sql_id($l_conn_id) . ", " . "isys_cats_database_gateway_list__type = " .
            $this->convert_sql_text($p_type) . ", " . "isys_cats_database_gateway_list__host = " . $this->convert_sql_text($p_host) . ", " .
            "isys_cats_database_gateway_list__port = " . $this->convert_sql_text($p_port) . ", " . "isys_cats_database_gateway_list__user = " .
            $this->convert_sql_text($p_user) . ", " . "isys_cats_database_gateway_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " .
            "isys_cats_database_gateway_list__description = " . $this->convert_sql_text($p_description);

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            return $l_last_id;
        } else {
            return false;
        }
    }

    public function get_target_schema($p_cats_id)
    {

        if (!is_null($p_cats_id)) {

            $l_sql = "SELECT * FROM isys_cats_database_gateway_list_2_isys_obj " .
                "INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_gateway_list_2_isys_obj.isys_obj__id " . "WHERE isys_cats_database_gateway_list__id = " .
                $this->convert_sql_id($p_cats_id);

            $l_res = $this->retrieve($l_sql);
            if ($l_res && $l_res->num_rows() > 0) {
                return $l_res;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

?>