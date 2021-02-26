<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 * DAO: global category for contract assignments.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_contract_assignment extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'contract_assignment';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CONTRACT_ASSIGNMENT';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'connected_contract';

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
     * Object ID field
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_contract_assignment_list__isys_obj__id';

    /**
     * Save global category contract assignment.
     *
     * @param    integer $p_cat_level
     * @param    integer & $p_intOldRecStatus
     *
     * @return   mixed
     * @version  Van Quyen Hoang <qhoang@synetics.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        if ($_GET[C__CMDB__GET__CATLEVEL] != -1 && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
            $l_ret = $this->save(
                $_GET[C__CMDB__GET__CATLEVEL],
                $p_intOldRecStatus,
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN'],
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN'],
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE']
            );
        } else {
            $l_ret = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN'],
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN'],
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS__HIDDEN'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE']
            );
            $p_cat_level = -1;
        }

        return $l_ret;
    }

    /**
     * Creates new category entry.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_rec_status
     * @param   null    $p_contract_start
     * @param   null    $p_contract_end
     * @param   integer $p_connected_contract
     * @param   string  $p_description
     *
     * @return  mixed
     */
    public function create(
        $p_obj_id,
        $p_rec_status = C__RECORD_STATUS__NORMAL,
        $p_contract_start = null,
        $p_contract_end = null,
        $p_connected_contract = null,
        $p_description = '',
        $p_reaction_rate = null
    ) {
        $l_dao_connection = new isys_cmdb_dao_connection($this->m_db);

        $l_sql = 'INSERT INTO isys_catg_contract_assignment_list (
				isys_catg_contract_assignment_list__isys_obj__id,
				isys_catg_contract_assignment_list__contract_start,
				isys_catg_contract_assignment_list__contract_end,
				isys_catg_contract_assignment_list__isys_connection__id,
				isys_catg_contract_assignment_list__reaction_rate__id,
				isys_catg_contract_assignment_list__description,
				isys_catg_contract_assignment_list__status) ' . 'VALUES( ' . $this->convert_sql_id($p_obj_id) . ', ' . $this->convert_sql_datetime($p_contract_start) . ', ' .
            $this->convert_sql_datetime($p_contract_end) . ', ' . $this->convert_sql_id($l_dao_connection->add_connection($p_connected_contract)) . ', ' .
            $this->convert_sql_id($p_reaction_rate) . ', ' . $this->convert_sql_text($p_description) . ', ' . $this->convert_sql_int($p_rec_status) . ')';

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_id = $this->get_last_insert_id();
            /* Create implicit relation */
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $l_relation_dao->handle_relation($l_id, "isys_catg_contract_assignment_list", defined_or_default('C__RELATION_TYPE__CONTRACT'), null, $p_connected_contract, $p_obj_id);

            return $l_id;
        } else {
            return false;
        }
    }

    /**
     * Updates existing category entry.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_rec_status
     * @param   null    $p_contract_start
     * @param   null    $p_contract_end
     * @param   integer $p_connected_contract
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save(
        $p_cat_id,
        $p_rec_status = C__RECORD_STATUS__NORMAL,
        $p_contract_start = null,
        $p_contract_end = null,
        $p_connected_contract = null,
        $p_description = '',
        $p_reaction_rate = null
    ) {
        $l_dao_connection = new isys_cmdb_dao_connection($this->m_db);
        $l_sql = 'SELECT isys_catg_contract_assignment_list__isys_obj__id,
					isys_catg_contract_assignment_list__isys_connection__id,
					isys_catg_contract_assignment_list__isys_catg_relation_list__id FROM isys_catg_contract_assignment_list ' .
            'WHERE isys_catg_contract_assignment_list__id = ' . $this->convert_sql_id($p_cat_id);
        $l_catdata = $this->retrieve($l_sql)
            ->get_row();
        $l_connection_id = $l_catdata['isys_catg_contract_assignment_list__isys_connection__id'];

        if (empty($l_connection_id)) {
            $l_connection_id = $l_dao_connection->add_connection($p_connected_contract);
        } else {
            $l_dao_connection->update_connection($l_connection_id, $p_connected_contract);
        }

        $l_sql = 'UPDATE isys_catg_contract_assignment_list ' . 'SET ' . 'isys_catg_contract_assignment_list__contract_start = ' .
            $this->convert_sql_datetime($p_contract_start) . ', ' . 'isys_catg_contract_assignment_list__contract_end = ' . $this->convert_sql_datetime($p_contract_end) .
            ', ' . 'isys_catg_contract_assignment_list__isys_connection__id = ' . $this->convert_sql_id($l_connection_id) . ', ' .
            'isys_catg_contract_assignment_list__reaction_rate__id = ' . $this->convert_sql_id($p_reaction_rate) . ', ' .
            'isys_catg_contract_assignment_list__description = ' . $this->convert_sql_text($p_description) . ' ' . 'WHERE isys_catg_contract_assignment_list__id = ' .
            $this->convert_sql_id($p_cat_id);

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $l_relation_dao->handle_relation(
                $p_cat_id,
                "isys_catg_contract_assignment_list",
                defined_or_default('C__RELATION_TYPE__CONTRACT'),
                $l_catdata['isys_catg_contract_assignment_list__isys_catg_relation_list__id'],
                $p_connected_contract,
                $l_catdata['isys_catg_contract_assignment_list__isys_obj__id']
            );

            return true;
        } else {
            return false;
        }
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   string  $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_contract_assignment_list
			INNER JOIN isys_obj ON isys_catg_contract_assignment_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_contract_assignment_list.isys_catg_contract_assignment_list__isys_connection__id
			LEFT JOIN isys_cats_contract_list ON isys_cats_contract_list__isys_obj__id = isys_connection__isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND isys_catg_contract_assignment_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_contract_assignment_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
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
                $l_sql = ' AND (isys_catg_contract_assignment_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_contract_assignment_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @return  array
     */
    protected function properties()
    {
        return [
            'connected_contract' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned contract'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_contract_assignment_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__CONTRACT'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_contract_assignment',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_contract_assignment',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'} \', "(", '
                            . 'IFNULL(' . self::build_query_date_format('isys_catg_contract_assignment_list__contract_start') . ', '
                            . 'IFNULL(' . self::build_query_date_format('isys_cats_contract_list__start_date') . ', \'-\')'
                            .'), " - ", '
                            . 'IFNULL(' . self::build_query_date_format('isys_catg_contract_assignment_list__contract_end') . ', '
                            . 'IFNULL(' . self::build_query_date_format('isys_cats_contract_list__end_date') . ', \'-\')'
                            .'), ")")
                            FROM isys_catg_contract_assignment_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contract_assignment_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                            LEFT JOIN isys_cats_contract_list on isys_cats_contract_list__isys_obj__id = isys_obj__id',
                        'isys_catg_contract_assignment_list',
                        'isys_catg_contract_assignment_list__id',
                        'isys_catg_contract_assignment_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_contract_assignment_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_contract_assignment_list',
                            'LEFT',
                            'isys_catg_contract_assignment_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_contract_assignment_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter' => 'C__CATS__CONTRACT'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'contract_start'     => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contract begin'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_contract_assignment_list__contract_start',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT IFNULL(' . self::build_query_date_format('isys_catg_contract_assignment_list__contract_start') . ', IFNULL(' . self::build_query_date_format('isys_cats_contract_list__start_date') . ', \'-\'))' .
                        ' FROM isys_catg_contract_assignment_list
                        INNER JOIN isys_connection ON isys_connection__id = isys_catg_contract_assignment_list__isys_connection__id
                        INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                        LEFT JOIN isys_cats_contract_list on isys_cats_contract_list__isys_obj__id = isys_obj__id',
                        'isys_catg_contract_assignment_list',
                        'isys_catg_contract_assignment_list__id',
                        'isys_catg_contract_assignment_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_contract_assignment_list__isys_obj__id'])
                    ),
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'contract_end'       => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contract end'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_contract_assignment_list__contract_end',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT IFNULL(' . self::build_query_date_format('isys_catg_contract_assignment_list__contract_end') . ', IFNULL(' . self::build_query_date_format('isys_cats_contract_list__end_date') . ', \'-\'))' .
                        ' FROM isys_catg_contract_assignment_list
                        INNER JOIN isys_connection ON isys_connection__id = isys_catg_contract_assignment_list__isys_connection__id
                        INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                        LEFT JOIN isys_cats_contract_list on isys_cats_contract_list__isys_obj__id = isys_obj__id',
                        'isys_catg_contract_assignment_list',
                        'isys_catg_contract_assignment_list__id',
                        'isys_catg_contract_assignment_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_contract_assignment_list__isys_obj__id'])
                    ),
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'reaction_rate'      => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__REACTION_RATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Reaction rate'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_contract_assignment_list__reaction_rate__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_contract_reaction_rate',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_contract_reaction_rate',
                        'isys_contract_reaction_rate__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_contract_reaction_rate__title FROM isys_catg_contract_assignment_list
                            INNER JOIN isys_contract_reaction_rate ON isys_contract_reaction_rate__id = isys_catg_contract_assignment_list__reaction_rate__id',
                        'isys_catg_contract_assignment_list',
                        'isys_catg_contract_assignment_list__id',
                        'isys_catg_contract_assignment_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_contract_assignment_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_contract_assignment_list',
                            'LEFT',
                            'isys_catg_contract_assignment_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_contract_reaction_rate',
                            'LEFT',
                            'isys_catg_contract_assignment_list__isys_contract_reaction_rate__id',
                            'isys_contract_reaction_rate__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_contract_reaction_rate'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'description'        => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_contract_assignment_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CONTRACT_ASSIGNMENT', 'C__CATG__CONTRACT_ASSIGNMENT')
                ],
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['contract_start'][C__DATA__VALUE],
                            $p_category_data['properties']['contract_end'][C__DATA__VALUE],
                            $p_category_data['properties']['connected_contract'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['reaction_rate'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['contract_start'][C__DATA__VALUE],
                            $p_category_data['properties']['contract_end'][C__DATA__VALUE],
                            $p_category_data['properties']['connected_contract'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['reaction_rate'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}
