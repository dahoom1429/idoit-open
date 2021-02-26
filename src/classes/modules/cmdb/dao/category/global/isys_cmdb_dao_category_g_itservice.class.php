<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for IT services.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_itservice extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'itservice';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__IT_SERVICE';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__IT_SERVICE;

    /**
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';

    /**
     * Array of inconsistence objects underneath of all IT-Services.
     *
     * @var  array
     */
    protected $m_inconsistence = [];

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
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
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Category main table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_its_components_list'; // function

    /**
     * @param       $p_object_id
     * @param array $p_objects
     *
     * @return bool
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = null;
        $l_current_its_dao = $this->get_data_by_object($p_object_id);

        while ($l_row = $l_current_its_dao->get_row()) {
            if (!in_array($l_row["isys_catg_its_components_list__isys_obj__id"], $p_objects)) {
                $l_delete[$l_row["isys_catg_its_components_list__id"]] = $l_row["isys_catg_its_components_list__isys_obj__id"];
            }

            $l_current_its[$l_row["isys_obj__id"]] = true;
        }

        if (isset($p_objects[0]) && !empty($p_objects[0])) {
            foreach ($p_objects as $l_its) {
                if (!isset($l_current_its) || !$l_current_its[$l_its]) {
                    $l_id = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_its);
                    unset($l_changes, $l_changes_compressed);
                }
            }
        }

        if (isset($l_delete) && is_array($l_delete)) {
            foreach ($l_delete as $l_deleteKey => $l_deleteObj) {
                $this->delete_entry($l_deleteKey, 'isys_catg_its_components_list');
            }
        }

        return $l_id;
    }

    /**
     * Do nothing
     *
     * @param      $p_cat_level
     * @param      $p_intOldRecStatus
     * @param bool $p_create
     *
     * @return null
     */
    public function save_element($p_cat_level, $p_intOldRecStatus, $p_create = false)
    {
        return null;
    }

    public function create($p_objID, $p_newRecStatus, $p_connectedObjID)
    {
        $l_dao_its_components = new isys_cmdb_dao_category_g_it_service_components($this->m_db);

        return $l_dao_its_components->create($p_connectedObjID, $p_newRecStatus, $p_objID);
    }

    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT count(isys_catg_its_components_list__id) AS count FROM isys_catg_its_components_list " .
            "INNER JOIN isys_connection ON isys_connection__id = isys_catg_its_components_list__isys_connection__id " . "WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= " AND (isys_connection__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_catg_its_components_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ");";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    }

    /**
     * Get data method.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_its_components_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_catg_its_components_list__isys_connection__id " .
            "INNER JOIN isys_obj ON isys_catg_its_components_list__isys_obj__id = isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_its_components_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_catg_its_components_list__status = '{$p_status}')";
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
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IT_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_its_components_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                              FROM isys_catg_its_components_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_its_components_list__isys_obj__id
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_its_components_list__isys_connection__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            'isys_obj.isys_obj__status = \'' . C__RECORD_STATUS__NORMAL . '\'',
                            'AND isys_catg_its_components_list__status = \'' . C__RECORD_STATUS__NORMAL . '\''
                        ]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_its_components_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_its_components_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_its_components_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__ITSERVICE__CONNECTED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        // @todo Property Callback for multiedit (in future).
                        'relationFilter' => "C__RELATION_TYPE__SOFTWARE;C__RELATION_TYPE__CLUSTER_SERVICE",
                        'catFilter'      => "C__CATG__SERVICE"
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => true
                ]
            ]),
            'sysid'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'SYSID',
                    C__PROPERTY__INFO__DESCRIPTION => 'SYSID'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__sysid'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__ITSERVICE__SYSID'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ]
            ])
        ];
    }

    /**
     * @param array $categoryData
     * @param int   $objectId
     * @param int   $status
     *
     * @return bool|mixed
     */
    public function sync($categoryData, $objectId, $status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($categoryData) && isset($categoryData['properties'])) {
            // Create category data identifier if needed.
            if ($status === isys_import_handler_cmdb::C__CREATE && $objectId > 0) {
                $success = true;

                // @see  ID-7607  We'll receive either int or array, so we unify the further processment.
                $connectionObjects = array_values((array)$categoryData['properties']['connected_object'][C__DATA__VALUE]);

                foreach ($connectionObjects as $connectionObjectId) {
                    if (!$this->create($objectId, C__RECORD_STATUS__NORMAL, $connectionObjectId)) {
                        $success = false;
                    }
                }

                // We always return true when every creation went well, in case of (at least) on failure, we return false.
                return $success;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the inconsistence child objects of all IT-Services.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     $this->get_its_relations()
     */
    public function get_inconsistence($p_obj_id = null)
    {
        if ($p_obj_id !== null) {
            return $this->m_inconsistence[$p_obj_id];
        }

        return $this->m_inconsistence;
    }

    /**
     * Method for retrieving all IT-Service relations and objects.
     *
     * @param   integer $p_its_obj_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_its_relations($p_its_obj_id)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao($g_comp_database);

        $l_return = [];
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id(defined_or_default('C__OBJTYPE__IT_SERVICE')) . '
			AND isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__id = ' . $l_dao->convert_sql_id($p_its_obj_id) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                if (!is_value_in_constants(
                    $l_row["isys_obj__isys_cmdb_status__id"],
                    ['C__CMDB_STATUS__IN_OPERATION', 'C__CMDB_STATUS__IDOIT_STATUS', 'C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE']
                )) {
                    $this->m_inconsistence[$l_row["isys_obj__id"]][$l_row["isys_obj__id"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                }

                $l_return[$l_row["isys_obj__id"]] = [
                    'cmdb_status' => $l_row["isys_obj__isys_cmdb_status__id"],
                    'cmdb_color'  => $l_row["isys_cmdb_status__color"],
                    'child'       => $this->recurse_relation($l_row["isys_obj__id"])
                ];

                $this->m_obj_arr = [];
            }
        }

        return $l_return;
    }

    /**
     * Recursive function to iterate through relations
     *
     * @param   integer $p_obj_id
     * @param   integer $p_it_service
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    private function recurse_relation($p_obj_id, $p_it_service = null)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_g_relation($g_comp_database);

        if ($p_it_service === null) {
            $p_it_service = $p_obj_id;
        }

        $l_return = [];
        $l_sql = "SELECT * FROM isys_catg_relation_list
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__master
			LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_catg_relation_list__isys_obj__id__slave = " . $l_dao->convert_sql_id($p_obj_id) . ' AND isys_obj__status = ' .
            $l_dao->convert_sql_id(C__RECORD_STATUS__NORMAL) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                if (is_null($this->m_obj_arr) || !in_array($l_row["isys_catg_relation_list__isys_obj__id__master"], $this->m_obj_arr)) {
                    $this->m_obj_arr[] = $p_obj_id;

                    if (!is_value_in_constants(
                        $l_row["isys_obj__isys_cmdb_status__id"],
                        ['C__CMDB_STATUS__IN_OPERATION', 'C__CMDB_STATUS__IDOIT_STATUS', 'C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE']
                    )) {
                        if (is_null($this->m_inconsistence[$p_it_service]) ||
                            !in_array($l_row["isys_catg_relation_list__isys_obj__id__master"], $this->m_inconsistence[$p_it_service])) {
                            $this->m_inconsistence[$p_it_service][$l_row["isys_catg_relation_list__isys_obj__id__master"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                        }
                    }

                    $l_return[$l_row['isys_obj__id']] = [
                        'cmdb_status' => $l_row['isys_obj__isys_cmdb_status__id'],
                        'cmdb_color'  => $l_row['isys_cmdb_status__color'],
                        'child'       => $this->recurse_relation($l_row['isys_catg_relation_list__isys_obj__id__master'], $p_it_service)
                    ];
                }
            }
        }

        return $l_return;
    }
}
