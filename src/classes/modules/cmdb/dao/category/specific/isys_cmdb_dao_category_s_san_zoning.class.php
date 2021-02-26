<?php

/**
 * i-doit
 *
 * DAO: Specific category for SAN zoning.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_san_zoning extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'san_zoning';

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
        $l_sql = "SELECT * FROM isys_obj
			LEFT JOIN isys_cats_san_zoning_list
			ON isys_obj__id = isys_cats_san_zoning_list__isys_obj__id
			WHERE isys_obj__isys_obj_type__id = " . $this->convert_sql_id($this->get_objtype_id_by_const_string("C__OBJTYPE__SAN_ZONING")) . "
			" . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_san_zoning_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_san_zoning_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ";");
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
                $l_sql = ' AND (isys_cats_san_zoning_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_cats_san_zoning_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_san_zoning_list__title'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__SAN_ZONING__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'members'     => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SAN_ZONING__MEMBERS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Members'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_san_zoning_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT DISTINCT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_cats_san_zoning_list
                                INNER JOIN isys_san_zoning_fc_port ON isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = isys_cats_san_zoning_list__id
                                INNER JOIN isys_catg_fc_port_list ON isys_catg_fc_port_list__id = isys_san_zoning_fc_port__isys_catg_fc_port_list__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_catg_fc_port_list__isys_obj__id', 'isys_cats_san_zoning_list', 'isys_cats_san_zoning_list__id',
                        'isys_cats_san_zoning_list__isys_obj__id', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_san_zoning_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_san_zoning_list', 'LEFT', 'isys_cats_san_zoning_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_san_zoning_fc_port', 'LEFT', 'isys_cats_san_zoning_list__id',
                            'isys_san_zoning_fc_port__isys_cats_san_zoning_list__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_san_zoning_fc_port__isys_catg_fc_port_list__id',
                            'isys_catg_fc_port_list__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__SAN_ZONING__MEMBERS',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        'p_strCatLevel'  => '',
                        'p_selectedWWNs' => '',
                        'p_strPopupType' => 'browser_object_ng',
                        'default'        => ''
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_san_zoning_members'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_san_zoning_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__SAN_ZONING', 'C__CATS__SAN_ZONING')
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
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            $l_members = $this->get_property('members');
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $this->get_property('title'), $this->get_property('description'));
                    if ($p_category_data['data_id']) {
                        $l_indicator = true;
                        if (is_array($l_members)) {
                            foreach ($l_members as $l_value) {
                                if (!empty($l_value['wwn_selected'])) {
                                    $l_arr_wwns[] = $l_value['ref_id'];
                                }
                                $l_arr_fcport[] = $l_value['ref_id'];
                            }
                            $this->attach_fc_ports($l_arr_fcport, $l_arr_wwns, $p_category_data['data_id']);
                        }
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $this->get_property('title'), $this->get_property('members'), null,
                        $this->get_property('description'));
                    $this->detach_fc_ports($p_category_data['data_id']);
                    if (is_array($l_members)) {
                        foreach ($l_members as $l_value) {
                            if (!empty($l_value['wwn_selected'])) {
                                $l_arr_wwns[] = $l_value['ref_id'];
                            }
                            $l_arr_fcport[] = $l_value['ref_id'];
                        }
                        $this->attach_fc_ports($l_arr_fcport, $l_arr_wwns, $p_category_data['data_id']);
                    }
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save element menthod.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_san_zoning_list__status"];
        $l_list_id = $l_catdata["isys_cats_san_zoning_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_san_zoning_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        if ($l_list_id != "") {
            $l_bRet = $this->save($l_list_id, C__RECORD_STATUS__NORMAL, $l_catdata["isys_obj__title"], $_POST['C__CATS__SAN_ZONING__FC_PORTS'],
                $_POST['C__CATS__SAN_ZONING__WWNS'], $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer  $p_cat_level
     * @param   integer  $p_newRecStatus
     * @param   string   $p_title
     * @param            $p_members
     * @param            $p_wwns
     * @param   string   $p_description
     *
     * @return  boolean true, if transaction executed successfully, else false
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_members, $p_wwns, $p_description)
    {
        $l_strSql = "UPDATE isys_cats_san_zoning_list SET
			isys_cats_san_zoning_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_san_zoning_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_cats_san_zoning_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_cats_san_zoning_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql) && $this->apply_update()) {
            if (!empty($p_members) || !empty($p_wwns)) {
                $this->detach_fc_ports($p_cat_level);
                $this->attach_fc_ports($p_members, $p_wwns, $p_cat_level);
            } else {
                $this->detach_fc_ports($p_cat_level);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Method for deleting all connected FC ports by a category-data ID.
     *
     * @param   integer $p_catlevel
     *
     * @return  boolean
     */
    public function detach_fc_ports($p_catlevel)
    {
        $l_delete = "DELETE FROM isys_san_zoning_fc_port
			WHERE isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = " . $this->convert_sql_id($p_catlevel) . ";";

        return ($this->update($l_delete) && $this->apply_update());
    }

    public function attach_fc_ports($p_members, $p_wwns, $p_catlevel)
    {
        if (!is_array($p_members)) {
            $l_members_arr = explode(",", $p_members);
        } else {
            $l_members_arr = $p_members;
        }

        if (!is_array($p_wwns)) {
            $l_arWWNs = explode(",", $p_wwns);
        } else {
            $l_arWWNs = $p_wwns;
        }

        $l_update = "INSERT INTO isys_san_zoning_fc_port (
			isys_san_zoning_fc_port__isys_cats_san_zoning_list__id,
			isys_san_zoning_fc_port__isys_catg_fc_port_list__id,
			isys_san_zoning_fc_port__port_selected,
			isys_san_zoning_fc_port__wwn_selected
			) VALUES ";

        foreach ($l_members_arr as $val) {
            if (!empty($val)) {
                $l_update .= "(" . $this->convert_sql_id($p_catlevel) . ", " . $this->convert_sql_id($val) . ", 1, ";
                if (in_array($val, $l_arWWNs, false)) {
                    $l_update .= "1";
                } else {
                    $l_update .= "0";
                }

                $l_update .= "),";
            }
        }

        foreach ($l_arWWNs as $l_wwn) {
            if (!empty($l_wwn)) {
                if (!in_array($l_wwn, $l_members_arr, false)) {
                    $l_update .= "(" . $this->convert_sql_id($p_catlevel) . ", " . $this->convert_sql_id($l_wwn) . ", 0, 1),";
                }
            }
        }

        $l_update = substr($l_update, 0, -1);

        return ($this->update($l_update) && $this->apply_update());
    }

    public function get_san_zoning_fc_ports($p_catlevel)
    {
        $l_return = [];
        $l_sql = "SELECT isys_san_zoning_fc_port__isys_catg_fc_port_list__id, isys_catg_fc_port_list__isys_obj__id FROM isys_san_zoning_fc_port
			LEFT JOIN isys_catg_fc_port_list ON isys_catg_fc_port_list__id = isys_san_zoning_fc_port__isys_catg_fc_port_list__id
			WHERE isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = " . $this->convert_sql_id($p_catlevel);
        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            $l_return[] = [
                'obj_id'     => $l_row["isys_catg_fc_port_list__isys_obj__id"],
                'fc_port_id' => $l_row["isys_san_zoning_fc_port__isys_catg_fc_port_list__id"]
            ];
        }

        return $l_return;
    }

    public function get_san_zoning_fc_port_result($p_fc_port_id = null, $p_zone_id)
    {

        $l_sql = "SELECT * FROM isys_san_zoning_fc_port WHERE TRUE AND isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = " . $this->convert_sql_id($p_zone_id);

        if ($p_fc_port_id) {
            $l_sql .= " AND isys_san_zoning_fc_port__isys_catg_fc_port_list__id = " . $this->convert_sql_id($p_fc_port_id);
        }

        $l_res = $this->retrieve($l_sql);

        if ($l_res->num_rows() > 0) {
            return $l_res;
        } else {
            return false;
        }

    }

    /**
     *
     * @param   integer $p_catlevel
     *
     * @return  string
     */
    public function get_selected_wwns($p_catlevel)
    {
        $l_return = [];
        $l_query = 'SELECT isys_san_zoning_fc_port__isys_catg_fc_port_list__id
			FROM isys_san_zoning_fc_port
			WHERE isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = ' . $this->convert_sql_id($p_catlevel) . '
			AND isys_san_zoning_fc_port__wwn_selected = 1';

        $l_res = $this->retrieve($l_query);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = (int)$l_row["isys_san_zoning_fc_port__isys_catg_fc_port_list__id"];
            }
        }

        return implode(',', $l_return);
    }

    /**
     * Retrieve an array of selected FC ports by a given port ID.
     *
     * @param   integer $p_catlevel
     *
     * @return  array
     */
    public function get_selected_fc_ports_by_fc_port_id($p_catlevel)
    {
        $l_return = [];
        $l_query = 'SELECT isys_san_zoning_fc_port__isys_cats_san_zoning_list__id
			FROM isys_san_zoning_fc_port
			WHERE isys_san_zoning_fc_port__isys_catg_fc_port_list__id = ' . $this->convert_sql_id($p_catlevel) . '
			AND isys_san_zoning_fc_port__port_selected = 1';

        $l_res = $this->retrieve($l_query);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = (int)$l_row["isys_san_zoning_fc_port__isys_cats_san_zoning_list__id"];
            }
        }

        return $l_return;
    }

    /**
     * Retrieve an array of selected WWNs by a given port ID.
     *
     * @param   integer $p_catlevel
     *
     * @return  array
     */
    public function get_selected_wwns_by_fc_port_id($p_catlevel)
    {
        $l_return = [];
        $l_query = 'SELECT isys_san_zoning_fc_port__isys_cats_san_zoning_list__id
			FROM isys_san_zoning_fc_port
			WHERE isys_san_zoning_fc_port__isys_catg_fc_port_list__id = ' . $this->convert_sql_id($p_catlevel) . '
			AND isys_san_zoning_fc_port__wwn_selected = 1';

        $l_res = $this->retrieve($l_query);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = (int)$l_row["isys_san_zoning_fc_port__isys_cats_san_zoning_list__id"];
            }
        }

        return $l_return;
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   string  $p_description
     *
     * @return  mixed  Integer of the newly created ID or false on failure.
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_description)
    {
        $l_strSql = "INSERT IGNORE INTO isys_cats_san_zoning_list SET
			isys_cats_san_zoning_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
			isys_cats_san_zoning_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_cats_san_zoning_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_san_zoning_list__status = " . $this->convert_sql_id($p_newRecStatus) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     *
     * @param   integer $p_fc_port_id
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_zones($p_fc_port_id)
    {
        $l_sql = 'SELECT *
			FROM isys_san_zoning_fc_port
			LEFT JOIN isys_cats_san_zoning_list__id = isys_san_zoning_fc_port__isys_cats_san_zoning_list__id
			WHERE isys_san_zoning_fc_port__isys_catg_fc_port_list__id = ' . $this->convert_sql_id($p_fc_port_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Retrieve an array of assigned SAN zones by a given port ID.
     *
     * @param   integer $p_fc_port_id
     *
     * @return  array
     */
    public function get_assigned_san_zones_id($p_fc_port_id)
    {
        $l_sql = "SELECT * FROM isys_san_zoning_fc_port " . "WHERE isys_san_zoning_fc_port__isys_catg_fc_port_list__id = " . $this->convert_sql_id($p_fc_port_id);
        $l_res = $this->retrieve($l_sql);

        $l_return = [];
        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = (int)$l_row["isys_san_zoning_fc_port__isys_cats_san_zoning_list__id"];
            }
        }

        return $l_return;
    }

    public function get_fc_port_subset($p_set)
    {
        $l_query = "SELECT * FROM isys_catg_fc_port_list WHERE FALSE";

        foreach ($p_set as $l_id) {
            $l_query .= " OR isys_catg_fc_port_list__id = " . $this->convert_sql_id($l_id);
        }

        return $this->retrieve($l_query);
    }

    /**
     * Method for receiving assigned FC ports.
     *
     * @param   integer $p_catlevel
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_fc_ports($p_catlevel)
    {
        $l_query = "SELECT isys_san_zoning_fc_port__isys_catg_fc_port_list__id
			FROM isys_san_zoning_fc_port
			WHERE isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = " . $this->convert_sql_id($p_catlevel) . "
			AND isys_san_zoning_fc_port__port_selected = 1;";

        return $this->retrieve($l_query);
    }

    /**
     * Method for retrieving assigned WWNs.
     *
     * @param   integer $p_catlevel
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_wwns($p_catlevel)
    {
        $l_sql = "SELECT isys_san_zoning_fc_port__isys_catg_fc_port_list__id
			FROM isys_san_zoning_fc_port
			WHERE isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = " . $this->convert_sql_id($p_catlevel) . "
			AND isys_san_zoning_fc_port__wwn_selected = 1;";

        return $this->retrieve($l_sql);
    }
}