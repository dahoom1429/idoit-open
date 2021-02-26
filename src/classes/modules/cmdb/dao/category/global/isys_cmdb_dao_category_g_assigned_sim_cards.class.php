<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;
use idoit\Component\Property\Type\ObjectBrowserSecondListProperty;
use idoit\Component\Property\Type\VirtualProperty;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for assigned sim cards
 *
 * @package     i-doit
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_assigned_sim_cards extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'assigned_sim_cards';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CATG__ASSIGNED_SIM_CARDS';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'isys_obj__id';

    /**
     * Determines if Category is multivalued or not
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
    protected $m_object_browser_property = 'isys_catg_cards_list__id';

    /**
     * Main table where properties are stored persistently.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_cards_list';

    /**
     * Field which holds the connected object id field if defined.
     *
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_catg_cards_list__isys_obj__id';

    /**
     * Create method.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_card_id
     * @param   integer $p_status
     *
     * @return  mixed  Integer with last inserted ID on success, boolean false on failure.
     */
    public function create($p_obj_id, $p_card_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        return true;
    }

    /**
     * @param int $objId
     * @param int $cardId
     * @param int $status
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function save($objId, $cardId, $status = C__RECORD_STATUS__NORMAL)
    {
        $cardData = $this->get_data($cardId)->get_row();
        $connectionId = $cardData['isys_catg_cards_list__isys_connection__id'];
        $update = 'UPDATE isys_catg_cards_list 
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id SET 
            isys_connection__isys_obj__id = ' . $this->convert_sql_id($objId) . ' 
            WHERE isys_catg_cards_list__id = ' . $this->convert_sql_id($cardId);

        if ($connectionId === null) {
            $connectionId = isys_cmdb_dao_connection::instance(isys_application::instance()->container->get('database'))
                ->add_connection($objId);

            $update = 'UPDATE isys_catg_cards_list SET isys_catg_cards_list__isys_connection__id = ' .
                $this->convert_sql_id($connectionId) . ' 
                WHERE isys_catg_cards_list__id = ' . $this->convert_sql_id($cardId);
        }

        $relationDao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
        $relationDao->handle_relation(
            $cardId,
            "isys_catg_cards_list",
            'C__RELATION_TYPE__ASSIGNED_SIM_CARDS',
            $cardData["isys_catg_cards_list__isys_catg_relation_list__id"],
            $objId,
            $cardData["isys_catg_cards_list__isys_obj__id"]
        );

        return $this->update($update) && $this->apply_update();
    }

    /**
     * @param array $cards
     *
     * @return bool
     * @throws isys_exception_dao
     */
    private function removeAlreadyAssignedCards(array $cards)
    {
        $query = 'UPDATE isys_catg_cards_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id 
            SET isys_connection__isys_obj__id = NULL
            WHERE isys_catg_cards_list__id IN (' . implode(',', array_map([$this, 'convert_sql_id'], $cards)) . ');';

        return $this->update($query) && $this->apply_update();
    }

    /**
     * @param int $objectId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getAttachedCards(int $objectId)
    {
        $query = 'SELECT isys_catg_cards_list__id FROM isys_catg_cards_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id
            WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($objectId);

        $result = $this->retrieve($query);
        if (count($result)) {
            $return = [];
            while ($row = $result->get_row()) {
                $return[] = $row['isys_catg_cards_list__id'];
            }
            return $return;
        }
        return [];
    }

    /**
     * Create element method, gets called from the object browser after confirming the selection.
     *
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    public function attachObjects($p_object_id, array $p_objects = [])
    {
        $assignedCards = $this->getAttachedCards($p_object_id);
        $createAssignments = array_diff($p_objects, $assignedCards);
        $deleteAssignments = array_diff($assignedCards, $p_objects);

        if (count($createAssignments) === 0 && count($deleteAssignments) === 0) {
            return true;
        }

        // Remove all assignments to thes cards
        $this->removeAlreadyAssignedCards(array_merge($createAssignments, $deleteAssignments));

        if (count($createAssignments) > 0) {
            foreach ($createAssignments as $l_entry) {
                $this->save($p_object_id, $l_entry);
            }
        }
    }

    /**
     * Delete entries by object id for this category
     *
     * @param int $p_obj_id
     *
     * @return bool
     */
    public function delete_entries_by_obj_id($p_obj_id)
    {
        if ($p_obj_id > 0) {
            $l_sql = 'DELETE FROM ' . $this->m_table . ' ' . 'WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

            return $this->update($l_sql);
        }

        return false;
    }

    /**
     * @param null $p_obj_id
     *
     * @return int
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null && $p_obj_id > 0) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        if ($l_obj_id > 0) {
            $query = 'SELECT COUNT(*) as cnt FROM isys_catg_cards_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id
            WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

            return $this->retrieve($query)->get_row_value('cnt');
        }

        return false;
    }

    /**
     * Get-data method.
     *
     * @param int|null   $p_catg_card_id
     * @param int|null   $p_obj_id
     * @param string $p_condition
     * @param string|null   $p_filter
     * @param int|null   $p_status
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_catg_card_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $query = 'SELECT * FROM isys_catg_cards_list
            LEFT JOIN isys_obj ON isys_obj__id = isys_catg_cards_list__isys_obj__id
            LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $query .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if ($p_catg_card_id !== null) {
            $query .= ' AND isys_catg_cards_list__id = ' . $this->convert_sql_id($p_catg_card_id) . ' ';
        }

        if ($p_status !== null) {
            $query .= ' AND isys_catg_cards_list__status = ' . $this->convert_sql_int($p_status) . ' ';
        }

        return $this->retrieve($query . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'isys_obj__id'                => (new ObjectBrowserConnectionBackwardProperty(
                'C__CMDB__CATG__ASSIGNED_SIM_CARD__ISYS_OBJ__ID',
                'LC__UNIVERSAL__OBJECT_TITLE',
                'isys_catg_cards_list__isys_obj__id',
                'isys_catg_cards_list',
                '',
                [],
                'C__CATG__CARDS'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__MULTIEDIT => false
            ]),
            'isys_catg_cards_list__id' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ASSIGNED_SIM_CARDS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned sim cards'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_cards_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_cards_list',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_cards_list__title
                                FROM isys_catg_cards_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_cards_list',
                        'isys_catg_cards_list__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_cards_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_cards_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_cards_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__ASSIGNED_SIM_CARDS__ID',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_object_ng',
                        isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                        isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER       => 'C__CATG__CARDS',
                        isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
                        isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                        isys_popup_browser_object_ng::C__SECOND_LIST      => [
                            'isys_cmdb_dao_category_g_assigned_sim_cards::object_browser',
                            [C__CMDB__GET__OBJECT => (isset($_GET[C__CMDB__GET__OBJECT]) ? $_GET[C__CMDB__GET__OBJECT] : 0)]
                        ],
                        isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT => 'isys_cmdb_dao_category_g_assigned_sim_cards::format_selection'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_assigned_sim_cards_export_helper',
                        'assignedCards'
                    ]
                ]
            ])
        ];
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     * @return array
     */
    protected function dynamic_properties()
    {
        return [
            '_title' => (new DynamicProperty(
                'LC__CMDB__CATG__ASSIGNED_SIM_CARDS',
                'isys_catg_cards_list__id',
                'isys_catg_cards_list',
                [
                    $this,
                    'dynamicPropertyTitle'
                ]
            ))->mergePropertyData([
                C__PROPERTY__DATA__JOIN        => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_cards_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_cards_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_obj',
                        'LEFT',
                        'isys_catg_cards_list__isys_obj__id',
                        'isys_obj__id'
                    )
                ]
            ])
        ];
    }

    /**
     * @param array $p_row
     *
     * @return string
     */
    public function dynamicPropertyTitle($p_row)
    {
        $cardId = $p_row['isys_catg_cards_list__id'];
        $sql = 'SELECT isys_catg_cards_list__title FROM isys_catg_cards_list 
            WHERE isys_catg_cards_list__id=' . $this->convert_sql_int($cardId) . ' LIMIT 1;';
        $l_res = $this->get_database_component()->query($sql);
        $l_row = $this->get_database_component()->fetch_row_assoc($l_res);
        if (!empty($l_row)) {
            $title = $l_row['isys_catg_cards_list__title'];
        } else {
            $title = isys_tenantsettings::get('gui.empty_value', '-');
        }
        return $title;
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                $l_id = $this->save($p_object_id, $p_category_data['properties']['isys_catg_cards_list__id'][C__DATA__VALUE]);
                if ($l_id > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return string|array
     * @throws Exception
     */
    public function object_browser($p_context, array $p_parameters)
    {
        global $g_comp_database;

        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                $l_dao_port = new isys_cmdb_dao_category_g_cards($g_comp_database);
                $l_res_port = $l_dao_port->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);

                if ($l_res_port->num_rows() > 0) {
                    while ($l_row_port = $l_res_port->get_row()) {
                        $l_return[] = [
                            '__checkbox__'                              => $l_row_port["isys_catg_cards_list__id"],
                            $language->get('LC__CMDB__CATG__CARDS__TITLE') => $l_row_port["isys_catg_cards_list__title"]
                        ];
                    }
                }

                return isys_format_json::encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                if (isset($p_parameters['preselection'])) {
                    $preselection = isys_format_json::is_json_array($p_parameters['preselection'])? isys_format_json::decode($p_parameters['preselection']): $p_parameters['preselection'];

                    $dao = isys_cmdb_dao_category_g_cards::instance(isys_application::instance()->container->get('database'));
                    $l_res = $dao->get_data(null, null, 'AND isys_catg_cards_list.isys_catg_cards_list__id IN (' . (is_array($preselection) ? implode(',', $preselection): $preselection) . ')');
                } else {
                    $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                    // Create this class, because we can't just use "this" or we'll get an exception "Database component not loaded!".
                    $l_dao = new isys_cmdb_dao_category_g_assigned_sim_cards($g_comp_database);
                    $l_res = $l_dao->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);
                }

                while ($l_row = $l_res->get_row()) {
                    // @see  ID-6220  Also return a 'first' selection.
                    $l_return['first'][] = (int)$l_row['isys_catg_cards_list__isys_obj__id'];

                    $l_return['second'][] = [
                        $l_row['isys_catg_cards_list__id'],
                        $l_row['isys_catg_cards_list__title']
                    ];
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = isys_cmdb_dao_category_g_cards::instance($this->m_db)->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_cards_list__id'],
                            $categoryRow['isys_catg_cards_list__title']
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__CMDB__CATG__CARDS__TITLE')
                    ],
                    'data' => $preselection
                ];
        }
    }

    /**
     * @param array $entryIds
     *
     * @return bool
     * @throws isys_exception_dao
     */
    private function detachAssignments(array $entryIds)
    {
        $update = 'UPDATE isys_catg_cards_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id
            SET isys_connection__isys_obj__id = null
            WHERE isys_catg_cards_list__id = %s';
        $relationDao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
        $dao = isys_cmdb_dao_category_g_cards::instance($this->get_database_component());

        foreach ($entryIds as $entryId) {
            $relationId = $dao->get_data($entryId)->get_row_value('isys_catg_cards_list__isys_catg_relation_list__id');

            if ($relationId) {
                $relationDao->delete_relation($relationId);
            }

            $this->update(sprintf($update, $this->convert_sql_id($entryId)));
        }

        return $this->apply_update();
    }

    /**
     * @param int    $entryIds
     * @param int    $direction
     * @param string $table
     * @param null   $checkMethod
     * @param false  $purge
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function rank_record($entryIds, $direction, $table, $checkMethod = null, $purge = false)
    {
        if (count($entryIds) === 0) {
            return true;
        }

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__QUICK_PURGE || $_POST[C__GET__NAVMODE] == C__NAVMODE__PURGE) {
            $direction = C__CMDB__RANK__DIRECTION_DELETE;
        }

        if ($direction == C__CMDB__RANK__DIRECTION_DELETE) {
            $this->detachAssignments((array) $entryIds);
        }

        return true;
    }

    /**
     * Format selection for the object browser.
     *
     * @param int|null $cardId
     *
     * @param null     $unused
     *
     * @return  string
     * @throws isys_exception_database
     */
    public function format_selection($cardId = null, $unused = null)
    {
        $dao = new isys_cmdb_dao_category_g_cards($this->m_db);
        $objPlugin = new isys_smarty_plugin_f_text();

        if (!$cardId) {
            return null;
        }

        $data = $dao->get_data($cardId)->get_row();
        return (!empty($data)) ? $data['isys_obj__title'] . " >> " . $data['isys_catg_cards_list__title']: null;
    }
}
