<?php

/**
 * i-doit
 *
 * DAO: global category for assigned QinQ SP-VLAN
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_qinq_sp extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'qinq_sp';

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
    protected $m_object_id_field = 'isys_catg_qinq_list__isys_obj__id';

    /**
     * Category's table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_qinq_list';

    /**
     * Save element
     *
     * @return bool success
     */
    public function save_element()
    {
        // Parse user's category data:
        $l_data = $this->parse_user_data();

        $l_category_data_id = intval($_POST[$this->m_category_const]);

        // Get existing category data:
        if (!isset($this->m_data)) {
            $this->m_data = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
                ->__to_array();
        }

        if (is_countable($this->m_data) && count($this->m_data) > 0) {
            $l_category_data_id = $this->m_data[$this->m_table . '__id'];
        }

        if (!$l_category_data_id) {
            $l_category_data_id = $this->create_connector($this->m_table, $_GET[C__CMDB__GET__OBJECT]);
        }

        $this->save($l_category_data_id, C__RECORD_STATUS__NORMAL, $l_data['spvlan'], $this->m_data['isys_obj__id'],
            $this->m_data[$this->m_table . "__isys_catg_relation_list__id"], $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);
    }

    /**
     * Save method.
     *
     * @param   integer $p_category_id
     * @param   integer $p_status
     * @param   integer $p_spvlan
     * @param   integer $p_object_id
     * @param   integer $p_relation_id
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save(&$p_category_id, $p_status = C__RECORD_STATUS__NORMAL, $p_spvlan, $p_object_id = null, $p_relation_id = null, $p_description = null)
    {
        // Get old data
        $l_old_data = $this->get_data($p_category_id)
            ->get_row();

        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());

        if (isset($p_spvlan) && is_numeric($p_spvlan)) {
            if (!isset($l_old_data['isys_catg_qinq_list__isys_connection__id']) && $p_spvlan) {
                // Create new connection
                $l_old_data['isys_catg_qinq_list__isys_connection__id'] = $l_connection->add_connection($p_spvlan);
            } else if ($l_old_data['isys_connection__isys_obj__id'] != $p_spvlan) {
                // Update isys_connection if needed
                $l_connection->update_connection($l_old_data["isys_catg_qinq_list__isys_connection__id"], $p_spvlan);
            }
        } else {
            $l_old_data['isys_catg_qinq_list__isys_connection__id'] = null;
        }

        // Build SQL statement
        $l_values = [];
        $l_values[] = $this->m_table . '__isys_connection__id' . ' = ' . $this->convert_sql_id($l_old_data['isys_catg_qinq_list__isys_connection__id']);

        if (isset($p_description)) {
            $l_values[] = $this->m_table . '__description' . ' = ' . $this->convert_sql_text($p_description);
        }

        $l_sql = 'UPDATE `' . $this->m_table . '` SET ' . implode(',', $l_values) . ' WHERE `' . $this->m_table . '__id` = ' . $this->convert_sql_id($p_category_id) . ';';

        // Update entry
        if ($this->update($l_sql) && $this->apply_update()) {
            // Handle relation
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->m_db);

            $l_relation_dao->handle_relation($p_category_id, $this->m_table, defined_or_default('C__RELATION_TYPE__LAYER2_TRANSPORT'),
                $l_old_data["isys_catg_qinq_list__isys_catg_relation_list__id"], $p_spvlan, $l_old_data["isys_catg_qinq_list__isys_obj__id"]);
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return true;
    }

    /**
     * Return child elements by parent id
     *
     * @param int $p_parent_id
     *
     * @return isys_component_dao_result
     */
    public function get_data_by_parent($p_parent_id)
    {
        if (is_array($p_parent_id)) {
            $l_condition = ' AND isys_connection__isys_obj__id IN (' . implode(',', $p_parent_id) . ')';
        } else {
            $l_condition = ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_parent_id);
        }

        return $this->get_data(null, null, $l_condition);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'spvlan'      => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__QINQ_SP__SPVLAN',
                    C__PROPERTY__INFO__DESCRIPTION => 'SP-VLAN'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_qinq_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__LAYER2_TRANSPORT'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_qinq_sp',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_qinq_sp',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_qinq_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_qinq_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id', 'isys_catg_qinq_list', 'isys_catg_qinq_list__id',
                        'isys_catg_qinq_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_qinq_list', 'LEFT', 'isys_catg_qinq_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_catg_qinq_list__isys_connection__id',
                            'isys_connection__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__QINQ_SP__SPVLAN',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => false,
                        'catFilter'      => 'C__CATG__QINQ_CE',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__SEARCH     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_qinq_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__QINQ_SP', 'C__CATG__QINQ_SP')
                ]
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
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done,
     *                 otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status == isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector($this->m_table, $p_object_id);
            }

            if (($p_status == isys_import_handler_cmdb::C__CREATE || $p_status == isys_import_handler_cmdb::C__UPDATE) && $p_category_data['data_id'] > 0) {
                $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_category_data['properties']['spvlan'][C__DATA__VALUE], null, null,
                    $p_category_data['properties']['description'][C__DATA__VALUE]);

                return $p_category_data['data_id'];
            }
        }

        return false;
    }

}
