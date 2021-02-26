<?php

/**
 * i-doit
 *
 * DAO: specific category for fiber channel switches.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_switch_fc extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'switch_fc';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

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

        $l_sql = "SELECT * FROM isys_cats_switch_fc_list " . "INNER JOIN isys_obj " . "ON " . "isys_obj__id = isys_cats_switch_fc_list__isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_switch_fc_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_switch_fc_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql . ";");
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
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_switch_fc_list__title'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__SWITCH_FC_TITLE'
                ]
            ]),
            'is_active'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SWITCH_FC_ACTIVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Active'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_switch_fc_list__unit_active',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE WHEN isys_cats_switch_fc_list__unit_active = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                    WHEN isys_cats_switch_fc_list__unit_active = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)',
                        'isys_cats_switch_fc_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_switch_fc_list', 'LEFT', 'isys_cats_switch_fc_list__isys_obj__id',
                            'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID      => 'C__CATS__SWITCH_FC_ACTIVE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ],
                    C__PROPERTY__UI__DEFAULT => '0'
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_switch_fc_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__SWITCH_FC', 'C__CATS__SWITCH_FC')
                ]
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $this->get_property('title'), $this->get_property('is_active'),
                        $this->get_property('description')))) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $this->get_property('title'), $this->get_property('is_active'),
                        $this->get_property('description'));
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save specific category switch_fc
     *
     * @param integer $p_cat_level
     * @param integer &$p_intOldRecStatus
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2006-12-06
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_switch_fc_list__status"];

        $l_list_id = $l_catdata['isys_cats_switch_fc_list__id'];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_switch_fc_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        $l_bRet = $this->save($l_list_id, C__RECORD_STATUS__NORMAL, $_POST["C__CATS__SWITCH_FC_TITLE"], $_POST["C__CATS__SWITCH_FC_ACTIVE"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int     $p_cat_level
     * @param int     $p_newRecStatus
     * @param String  $p_title
     * @param boolean $p_active
     * @param String  $p_description
     *
     * @return  boolean
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_active, $p_description)
    {
        $l_strSql = "UPDATE isys_cats_switch_fc_list SET " . "isys_cats_switch_fc_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_switch_fc_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_cats_switch_fc_list__unit_active = " .
            $this->convert_sql_boolean($p_active) . ", " . "isys_cats_switch_fc_list__status = " . $this->convert_sql_id($p_newRecStatus) . " " .
            "WHERE isys_cats_switch_fc_list__id = " . $this->convert_sql_id($p_cat_level);

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry
     *
     * @param int     $p_objID
     * @param int     $p_newRecStatus
     * @param String  $p_title
     * @param boolean $p_active
     * @param String  $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_active, $p_description)
    {
        $l_strSql = "INSERT IGNORE INTO isys_cats_switch_fc_list SET " . "isys_cats_switch_fc_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_switch_fc_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_cats_switch_fc_list__unit_active = " . $this->convert_sql_id($p_active) .
            ", " . "isys_cats_switch_fc_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " . "isys_cats_switch_fc_list__isys_obj__id = " .
            $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }
}