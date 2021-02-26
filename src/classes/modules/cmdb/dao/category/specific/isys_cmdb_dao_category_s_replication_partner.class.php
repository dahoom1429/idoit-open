<?php

/**
 * i-doit
 *
 * DAO: specific category for replication partners.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_replication_partner extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'replication_partner';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'replication_partner';

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
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_replication_partner_list__isys_obj__id';

    /**
     * @param      $p_cat_level
     * @param      $p_intOldRecStatus
     * @param bool $p_create
     *
     * @return bool|int
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {

        $l_catdata = $this->get_result()
            ->__to_array();
        $p_initOldRec_status = $l_catdata["isys_cats_replication_partner_list__status"];
        $l_bRet = false;

        if (!isys_glob_get_param(C__CMDB__GET__CATLEVEL)) {
            return $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $_POST["C__CATS__REPLICATION_PARTNER__OBJ__HIDDEN"],
                $_POST["C__CATS__REPLICATION_PARTNER__TYPE"], $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);
        } else {
            $l_bRet = $this->save($l_catdata["isys_cats_replication_partner_list__id"], C__RECORD_STATUS__NORMAL, $_POST["C__CATS__REPLICATION_PARTNER__OBJ__HIDDEN"],
                $_POST["C__CATS__REPLICATION_PARTNER__TYPE"], $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $l_catdata["isys_cats_replication_partner_list__isys_connection__id"]);
        }

        return $l_bRet;
    }

    /**
     * @param $p_cat_level
     * @param $p_newRecStatus
     * @param $p_replication_partner
     * @param $p_replication_type
     * @param $p_description
     * @param $p_connectionID
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function save($p_cat_level, $p_newRecStatus, $p_replication_partner, $p_replication_type, $p_description, $p_connectionID)
    {
        $l_sql = "UPDATE isys_cats_replication_partner_list " . "SET " . "isys_cats_replication_partner_list__isys_connection__id = " .
            $this->convert_sql_id($this->handle_connection($p_cat_level, $p_replication_partner)) . " ," . "isys_cats_replication_partner_list__isys_replication_type__id = " .
            $this->convert_sql_id($p_replication_type) . " ," . "isys_cats_replication_partner_list__description = " . $this->convert_sql_text($p_description) . " " .
            "WHERE isys_cats_replication_partner_list__id = " . $this->convert_sql_id($p_cat_level) . " ;";
        if ($this->update($l_sql) && $this->apply_update()) {

            /**
             * Handle relation
             */
            $l_catdata = $this->get_data($p_cat_level)
                ->get_row();
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_dao_relation->handle_relation($p_cat_level, "isys_cats_replication_partner_list", defined_or_default('C__RELATION_TYPE__REPLICATION_PARTNER'),
                $l_catdata["isys_cats_replication_partner_list__isys_catg_relation_list__id"], $p_replication_partner,
                $l_catdata["isys_cats_replication_partner_list__isys_obj__id"]);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $p_objID
     * @param $p_newRecStatus
     * @param $p_replication_partner
     * @param $p_replication_type
     * @param $p_description
     *
     * @return bool|int
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function create($p_objID, $p_newRecStatus, $p_replication_partner, $p_replication_type, $p_description)
    {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_sql = "INSERT INTO isys_cats_replication_partner_list SET " . "isys_cats_replication_partner_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . " ," .
            "isys_cats_replication_partner_list__isys_replication_type__id = " . $this->convert_sql_id($p_replication_type) . " ," .
            "isys_cats_replication_partner_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_replication_partner)) . " ," .
            "isys_cats_replication_partner_list__status = " . C__RECORD_STATUS__NORMAL . " ," . "isys_cats_replication_partner_list__description = " .
            $this->convert_sql_text($p_description) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            /**
             * Handle relation
             */
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_dao_relation->handle_relation($l_last_id, "isys_cats_replication_partner_list", defined_or_default('C__RELATION_TYPE__REPLICATION_PARTNER'), null, $p_replication_partner, $p_objID);

            return $l_last_id;
        } else {
            return false;
        }
    }

    /**
     * Get-data method.
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
        $l_sql = "SELECT * FROM isys_cats_replication_partner_list " . "INNER JOIN isys_obj ON isys_cats_replication_partner_list__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_replication_type ON isys_replication_type__id = isys_cats_replication_partner_list__isys_replication_type__id " .
            "LEFT JOIN isys_connection ON isys_connection__id = isys_cats_replication_partner_list__isys_connection__id " . "WHERE TRUE";

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_replication_partner_list__id  = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_replication_partner_list__status) = '{$p_status}'";
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
            'type'                => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__REPLICATION__REPLICATION_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Replicationtype'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_replication_partner_list__isys_replication_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_replication_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_replication_type',
                        'isys_replication_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_replication_type__title
                            FROM isys_cats_replication_partner_list
                            INNER JOIN isys_replication_type ON isys_replication_type__id = isys_cats_replication_partner_list__isys_replication_type__id',
                        'isys_cats_replication_partner_list', 'isys_cats_replication_partner_list__id', 'isys_cats_replication_partner_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_replication_partner_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_replication_partner_list', 'LEFT',
                            'isys_cats_replication_partner_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_replication_type', 'LEFT',
                            'isys_cats_replication_partner_list__isys_replication_type__id', 'isys_replication_type__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__REPLICATION_PARTNER__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_replication_type'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'replication_partner' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__REPLICATION_PARTNER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Target object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_replication_partner_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__REPLICATION_PARTNER'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_replication_partner',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_replication_partner',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_replication_partner_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_replication_partner_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id', 'isys_cats_replication_partner_list',
                        'isys_cats_replication_partner_list__id', 'isys_cats_replication_partner_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_replication_partner_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_replication_partner_list', 'LEFT',
                            'isys_cats_replication_partner_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_cats_replication_partner_list__isys_connection__id',
                            'isys_connection__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__REPLICATION_PARTNER__OBJ',
                    C__PROPERTY__UI__PARAMS => [],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_replication_partner_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_replication_partner_list__description FROM isys_cats_replication_partner_list',
                        'isys_cats_replication_partner_list', 'isys_cats_replication_partner_list__id', 'isys_cats_replication_partner_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_replication_partner_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__REPLICATION_PARTNER', 'C__CATS__REPLICATION_PARTNER')
                ]
            ])
        ];
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|int
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $this->get_property('replication_partner'),
                        $this->get_property('type'), $this->get_property('description')))) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $this->get_property('replication_partner'), $this->get_property('type'),
                        $this->get_property('description'), null);
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Retrieve an object by  a given connection-ID.
     *
     * @param   integer $p_connectionID
     *
     * @return  array
     */
    public function get_obj_by_connection($p_connectionID)
    {
        $l_sql = "SELECT isys_connection__isys_obj__id, isys_obj__id, isys_obj__title FROM isys_connection " .
            "INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id " . "WHERE isys_connection__id = " . $this->convert_sql_id($p_connectionID);

        return $this->retrieve($l_sql)
            ->get_row();
    }
}

?>
