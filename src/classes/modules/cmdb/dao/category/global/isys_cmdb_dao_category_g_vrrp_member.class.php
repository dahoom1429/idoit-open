<?php

/**
 * i-doit
 *
 * CMDB DAO: Global category for VRRP members.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_vrrp_member extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'vrrp_member';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean  Defaults to false.
     */
    protected $m_multivalued = true;

    /**
     * Create new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        $l_result = parent::create_data($p_data);

        if ($l_result > 0) {
            // This is necessary to create the relations and stuff.
            $this->save_data($l_result, $p_data);
        }

        return $l_result;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'member'      => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VRRP_MEMBER__VRRP_MEMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Member'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_vrrp_member_list__isys_catg_log_port_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_log_port_list',
                        'isys_catg_log_port_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' > \', isys_catg_log_port_list__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_vrrp_member_list
                                INNER JOIN isys_catg_log_port_list ON isys_catg_log_port_list__id = isys_catg_vrrp_member_list__isys_catg_log_port_list__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_catg_log_port_list__isys_obj__id',
                        'isys_catg_vrrp_member_list',
                        'isys_catg_vrrp_member_list__id',
                        'isys_catg_vrrp_member_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_vrrp_member_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_vrrp_member_list',
                            'LEFT',
                            'isys_catg_vrrp_member_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_log_port_list',
                            'LEFT',
                            'isys_catg_vrrp_member_list__isys_catg_log_port_list__id',
                            'isys_catg_log_port_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_log_port_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__VRRP_MEMBER__MEMBER',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__TITLE              => 'LC__BROWSER__TITLE__LOG_PORT',
                        isys_popup_browser_object_ng::C__CAT_FILTER         => 'C__CATG__NETWORK;C__CATG__NETWORK_LOG_PORT',
                        isys_popup_browser_object_ng::C__SECOND_SELECTION   => true,
                        isys_popup_browser_object_ng::C__SECOND_LIST        => 'isys_cmdb_dao_category_g_vrrp_member::object_browser',
                        isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT => 'isys_cmdb_dao_category_g_vrrp_member::format_selection',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'vrrp_member_get_logical_port'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_vrrp_member_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_vrrp_member_list__description FROM isys_catg_vrrp_member_list',
                        'isys_catg_vrrp_member_list',
                        'isys_catg_vrrp_member_list__id',
                        'isys_catg_vrrp_member_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_vrrp_member_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VRRP_MEMBER', 'C__CATG__VRRP_MEMBER')
                ]
            ])
        ];
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_id   Entity's identifier
     * @param   array   $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_id, $p_data)
    {
        $l_result = parent::save_data($p_id, $p_data);

        if ($l_result) {
            $l_target_object = null;
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_relation_id = $this->retrieve('SELECT isys_catg_vrrp_member_list__isys_catg_relation_list__id FROM isys_catg_vrrp_member_list WHERE isys_catg_vrrp_member_list__id = ' .
                $this->convert_sql_id($p_id) . ';')
                ->get_row_value('isys_catg_vrrp_member_list__isys_catg_relation_list__id');

            if (isset($p_data['member']) && $p_data['member'] > 0) {
                $l_target_object = $this->retrieve('SELECT isys_catg_log_port_list__isys_obj__id FROM isys_catg_log_port_list WHERE isys_catg_log_port_list__id = ' .
                    $this->convert_sql_id($p_data['member']) . ';')
                    ->get_row_value('isys_catg_log_port_list__isys_obj__id');
            }

            // Handle the implicit relation, the relation-direction needs to be forced! No "default" direction here.
            $l_relation_dao->handle_relation($p_id, "isys_catg_vrrp_member_list", defined_or_default('C__RELATION_TYPE__VRRP'), $l_relation_id, $l_target_object, $p_data['isys_obj__id']);

            // Handle the parallel relation.
            $this->handle_parallel_relation($p_data['isys_obj__id']);
        }

        return $l_result;
    }

    /**
     * Method for second-selection object browser: handle preselection and provide content.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return string|array
     * @throws \idoit\Exception\JsonException
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                $firstSelection = [];
                $secondSelection = isys_format_json::is_json($p_parameters['preselection']) ? (array) isys_format_json::decode($p_parameters['preselection']) : [];

                // @see  ID-6220  Also return a 'first' selection.
                if (is_array($secondSelection)) {
                    foreach ($secondSelection as $categoryEntryId) {
                        $firstSelection[] = (int)$this->get_data($categoryEntryId)->get_row_value('isys_obj__id');
                    }
                }

                return [
                    'category' => [],
                    'first'    => $firstSelection,
                    'second'   => $secondSelection
                ];

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                $l_return = [];

                $l_res = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component())
                    ->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

                if (is_countable($l_res) && count($l_res)) {
                    $l_title = $language->get('LC__CMDB__CATG__INTERFACE_L__TITLE');
                    $l_mac = $language->get('LC__CMDB__CATG__PORT__MAC');

                    while ($l_row = $l_res->get_row()) {
                        $l_return[] = [
                            '__checkbox__' => $l_row['isys_catg_log_port_list__id'],
                            $l_title       => $l_row['isys_catg_log_port_list__title'],
                            $l_mac         => $l_row['isys_catg_log_port_list__mac']
                        ];
                    }
                }

                return isys_format_json::encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component())->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_log_port_list__id'],
                            $categoryRow['isys_catg_log_port_list__title'],
                            $categoryRow['isys_catg_log_port_list__mac'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title'])
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__CMDB__CATG__INTERFACE_L__TITLE'),
                        $language->get('LC__CMDB__CATG__PORT__MAC'),
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE')
                    ],
                    'data'   => $preselection
                ];
        }
    }

    /**
     * Method for formatting the object browser selection (logical ports).
     *
     * @param   integer $p_port_cat_id
     * @param   boolean $p_plain
     *
     * @return  string
     */
    public function format_selection($p_port_cat_id, $p_plain = false)
    {
        $l_port_row = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component())
            ->get_data($p_port_cat_id)
            ->get_row();
        $l_title = isys_application::instance()->container->get('language')
                ->get($l_port_row['isys_obj_type__title']) . ' >> ' . isys_application::instance()->container->get('language')
                ->get($l_port_row['isys_obj__title']) . ' >> ' . isys_application::instance()->container->get('language')
                ->get($l_port_row['isys_catg_log_port_list__title']);

        if ($p_plain) {
            return $l_title;
        }

        return isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($l_port_row['isys_obj__id'], $l_title, C__LINK__CATG, false, [
                C__CMDB__GET__CATG     => defined_or_default('C__CATG__NETWORK_LOG_PORT'),
                C__CMDB__GET__CATLEVEL => $p_port_cat_id
            ]);
    }

    /**
     * Method for retrieving all connected logical ports of a VRRP.
     *
     * @param   integer $p_vrrp_obj_id
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_vrrp_members($p_vrrp_obj_id, $p_status = null)
    {
        $l_sql = 'SELECT
		    isys_obj__id,
		    isys_catg_log_port_list__id,
		    isys_catg_log_port_list__title
		    FROM isys_catg_vrrp_member_list
			INNER JOIN isys_catg_log_port_list ON isys_catg_log_port_list__id = isys_catg_vrrp_member_list__isys_catg_log_port_list__id
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_log_port_list__isys_obj__id
			WHERE isys_catg_vrrp_member_list__isys_obj__id = ' . $this->convert_sql_id($p_vrrp_obj_id);

        if ($p_status !== null) {
            $p_status = $this->convert_sql_int($p_status);

            $l_sql .= ' AND isys_catg_vrrp_member_list__status = ' . $p_status . '
				AND isys_catg_log_port_list__status = ' . $p_status . '
				AND isys_obj__status = ' . $p_status . ';';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * @param   integer $p_object_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    protected function handle_parallel_relation($p_object_id)
    {
        $l_relation_pool_id = $this->get_relation_pool_by_id($p_object_id, isys_application::instance()->container->get('language')
            ->get('LC__CATG__STACK_MEMBER'));

        if ($l_relation_pool_id > 0) {
            $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $l_dao_relpool = isys_cmdb_dao_category_s_parallel_relation::instance($this->m_db);

            if ($l_dao_relpool->clear($l_relation_pool_id)) {
                // Get every stack member of the current object.
                $l_res = $this->get_data(null, $p_object_id, '', null, C__RECORD_STATUS__NORMAL);

                if (is_countable($l_res) && count($l_res)) {
                    while ($l_row = $l_res->get_row()) {
                        if ($l_row['isys_catg_vrrp_member_list__isys_catg_relation_list__id'] > 0) {
                            // And add it to the parallel relation.
                            $l_dao_relpool->attach_relation(
                                $l_relation_pool_id,
                                $l_dao_relation->get_data_by_id($l_row['isys_catg_vrrp_member_list__isys_catg_relation_list__id'])
                                    ->get_row_value('isys_obj__id')
                            );
                        }
                    }
                }
            }

            // Saves relpool ID.
            $this->update("UPDATE isys_catg_vrrp_member_list
                SET isys_catg_vrrp_member_list__isys_cats_relpool_list__id = " . $this->convert_sql_id($l_relation_pool_id) . "
                WHERE isys_catg_vrrp_member_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";");

            return $this->apply_update();
        }

        return false;
    }

    /**
     * Checks if a relation pool exists for the given object. Creates one if not.
     *
     * @param   integer $p_object_id
     * @param   string  $p_relation_title
     *
     * @return  integer
     */
    private function get_relation_pool_by_id($p_object_id, $p_relation_title)
    {
        $l_sql = 'SELECT isys_catg_vrrp_member_list__isys_cats_relpool_list__id, isys_cats_relpool_list__threshold, isys_cats_relpool_list__description
			FROM isys_catg_vrrp_member_list
			INNER JOIN isys_cats_relpool_list ON isys_cats_relpool_list__id = isys_catg_vrrp_member_list__isys_cats_relpool_list__id
			WHERE isys_catg_vrrp_member_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
			LIMIT 1;';

        $l_ret = $this->retrieve($l_sql)
            ->get_row();

        $l_dao_relpool = isys_cmdb_dao_category_s_parallel_relation::instance($this->m_db);

        if ($l_ret["isys_catg_vrrp_member_list__isys_cats_relpool_list__id"] > 0) {
            $l_dao_relpool->save(
                $l_ret["isys_catg_vrrp_member_list__isys_cats_relpool_list__id"],
                $p_relation_title,
                $l_ret["isys_cats_relpool_list__threshold"],
                $l_ret["isys_cats_relpool_list__description"]
            );

            return $l_ret["isys_catg_vrrp_member_list__isys_cats_relpool_list__id"];
        } else {
            return $l_dao_relpool->create($this->create_object($p_relation_title, defined_or_default('C__OBJTYPE__PARALLEL_RELATION')), $p_relation_title, 0, '');
        }
    }
}
