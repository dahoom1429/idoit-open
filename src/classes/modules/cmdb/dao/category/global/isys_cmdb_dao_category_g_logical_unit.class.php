<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;

/**
 * i-doit
 *
 * DAO: logical unit
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_logical_unit extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'logical_unit';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_logical_unit_list__isys_obj__id__parent';

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
    protected $m_object_id_field = 'isys_catg_logical_unit_list__isys_obj__id';

    protected $m_relation = []; // function

    /**
     * @var array
     */
    private static $objTypeIds = [];

    /**
     * Return child elements by parent ID.
     *
     * @param mixed   $p_parent_id
     * @param boolean $p_consider_rights
     * @param null    $status
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_parent($p_parent_id, $p_consider_rights = false, $status = C__RECORD_STATUS__NORMAL)
    {
        if (is_null($p_parent_id) || $p_parent_id == defined_or_default('C__OBJ__ROOT_LOCATION')) {
            if (isys_tenantsettings::get('cmdb.logical-location.object-type-filter', 1)) {
                $l_condition = ' AND isys_obj_type__const = \'C__OBJTYPE__WORKSTATION\'';
            } else {
                $l_condition = ' AND isys_obj_type__id IN (
                    SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg 
                    WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = (
                        SELECT isysgui_catg__id FROM isysgui_catg WHERE isysgui_catg__const = \'C__CATG__ASSIGNED_LOGICAL_UNIT\'
                    )
                 )';
            }
        } elseif (is_array($p_parent_id)) {
            $l_condition = ' AND isys_catg_logical_unit_list__isys_obj__id__parent IN (' . implode(',', $p_parent_id) . ')';
        } else {
            $l_condition = ' AND isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($p_parent_id);
        }

        $l_condition .= ' AND mainObject.isys_obj__status = ' . $this->convert_sql_int($status);

        if ($p_consider_rights) {
            $l_condition = isys_auth_cmdb_objects::instance()
                    ->get_allowed_objects_condition() . ' ' . $l_condition;
        }

        return $this->get_data(null, null, $l_condition, null, $status);
    }

    /**
     * Retrieves the logical parent of the given object.
     *
     * @param   integer $p_object_id
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_parent_by_id($p_object_id)
    {
        $l_sql = 'SELECT isys_catg_logical_unit_list__isys_obj__id__parent
            FROM isys_catg_logical_unit_list
            WHERE isys_catg_logical_unit_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
            LIMIT 1';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_logical_unit_list__isys_obj__id__parent');
    }

    /**
     * Method for searching objects by their title, which are located logically.
     *
     * @param   string  $p_title
     * @param   boolean $p_physically_located
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function search_located_objects_by_title($p_title, $p_physically_located = false, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_status_condition = ($p_status !== null) ? '' : 'AND isys_obj__STATUS = ' . $this->convert_sql_int($p_status);

        // This will be used if you also want to select physically located objects.
        if ($p_physically_located) {
            $l_sql = 'SELECT main.isys_obj__id, main.isys_obj__title, main.isys_obj__isys_obj_type__id,
				first_lvl.isys_catg_logical_unit_list__isys_obj__id__parent AS parent, p_loc.isys_obj__title AS parent_title,
				p_loc.isys_obj__isys_obj_type__id AS parent_objtype
				FROM isys_obj AS main
				LEFT JOIN isys_obj_type ON isys_obj_type__id = main.isys_obj__isys_obj_type__id
				LEFT JOIN isys_obj_type_2_isysgui_catg ON isys_obj_type_2_isysgui_catg__isys_obj_type__id = isys_obj_type__id
				LEFT JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
				LEFT JOIN isys_catg_logical_unit_list AS first_lvl ON first_lvl.isys_catg_logical_unit_list__isys_obj__id = main.isys_obj__id
				LEFT JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = main.isys_obj__id
				LEFT JOIN isys_obj AS p_loc ON p_loc.isys_obj__id = first_lvl.isys_catg_logical_unit_list__isys_obj__id__parent
				WHERE  main.isys_obj__title LIKE "%' . $this->m_db->escape_string($p_title) . '%"
				AND (first_lvl.isys_catg_logical_unit_list__isys_obj__id__parent > 0 OR isys_catg_location_list__parentid > 0) GROUP BY main.isys_obj__id';
        } else {
            $l_sql = 'SELECT main.isys_obj__id, main.isys_obj__title, main.isys_obj__isys_obj_type__id,
				first_lvl.isys_catg_logical_unit_list__isys_obj__id__parent AS parent, p_loc.isys_obj__title AS parent_title,
				p_loc.isys_obj__isys_obj_type__id AS parent_objtype
				FROM isys_obj AS main
				LEFT JOIN isys_obj_type ON isys_obj_type__id = main.isys_obj__isys_obj_type__id
				LEFT JOIN isys_obj_type_2_isysgui_catg ON isys_obj_type_2_isysgui_catg__isys_obj_type__id = isys_obj_type__id
				LEFT JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
				LEFT JOIN isys_catg_logical_unit_list AS first_lvl ON first_lvl.isys_catg_logical_unit_list__isys_obj__id = main.isys_obj__id
				LEFT JOIN isys_obj AS p_loc ON p_loc.isys_obj__id = first_lvl.isys_catg_logical_unit_list__isys_obj__id__parent
				WHERE main.isys_obj__title LIKE "%' . $this->m_db->escape_string($p_title) . '%"
				AND first_lvl.isys_catg_logical_unit_list__isys_obj__id__parent > 0 GROUP BY main.isys_obj__id';
        }

        return $this->retrieve($l_sql . $l_status_condition);
    }

    /**
     * @return mixed|void
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
            $l_category_data_id = $this->create_connector('isys_catg_logical_unit_list', $_GET[C__CMDB__GET__OBJECT]);
        }

        $this->save(
            $l_category_data_id,
            $l_data['parent'],
            C__RECORD_STATUS__NORMAL,
            $this->m_data['isys_obj__id'],
            $this->m_data[$this->m_table . "__isys_catg_relation_list__id"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );
    }

    /**
     * Save method.
     *
     * @param   integer $p_category_id
     * @param   integer $p_parent
     * @param   integer $p_status
     * @param   integer $p_object_id
     * @param   integer $p_relation_id
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_category_id, $p_parent, $p_status = C__RECORD_STATUS__NORMAL, $p_object_id = null, $p_relation_id = null, $p_description = null)
    {
        $l_sql = "UPDATE isys_catg_logical_unit_list
			SET isys_catg_logical_unit_list__isys_obj__id__parent = " . $this->convert_sql_id($p_parent) . ",
			isys_catg_logical_unit_list__description = " . $this->convert_sql_text($p_description) . "
			WHERE isys_catg_logical_unit_list__id = " . $this->convert_sql_id($p_category_id) . ";";

        $l_return = false;

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_return = true;
            if (!isset($p_relation_id) || !isset($p_object_id)) {
                $this->m_data = $this->get_data($p_category_id)
                    ->get_row();

                $p_relation_id = $this->m_data[$this->m_table . "__isys_catg_relation_list__id"];
                $p_object_id = $this->m_data[$this->m_table . "__isys_obj__id"];
            }

            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_dao->handle_relation($p_category_id, $this->m_table, $this->m_relation['type'], $p_relation_id, $p_parent, $p_object_id);

            $objTypeID = $this->get_objTypeID($p_object_id);
            // Remove location if exists and if current object is a workstation See ID-4425
            if ($this->objtype_is_catg_assigned($objTypeID, filter_defined_constants(['C__CATG__LOCATION', 'C__CATG__ASSIGNED_LOGICAL_UNIT'])) &&
                isys_tenantsettings::get('cmdb.logical-location.handle-location-inheritage', false)) {
                if ($p_parent) {
                    $locationDao = isys_cmdb_dao_category_g_location::instance($this->get_database_component());
                    $locationEntry = $locationDao->retrieve('SELECT isys_catg_location_list__id, isys_catg_location_list__parentid FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' .
                        $locationDao->convert_sql_id($p_object_id))
                        ->get_row();

                    if ($locationEntry) {
                        $locationEntryID = $locationEntry['isys_catg_location_list__id'];
                        $oldParentObjectID = $locationEntry['isys_catg_location_list__parentid'];
                        $newParentObjectID = $locationDao->retrieve('SELECT isys_catg_location_list__parentid FROM isys_catg_location_list 
                            WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($p_parent))
                            ->get_row_value('isys_catg_location_list__parentid');

                        $locationDao->handle_location_inheritage(false, $objTypeID, $locationEntryID, $p_object_id, $newParentObjectID, $oldParentObjectID);
                    }
                }
            }
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_return;
    }

    /**
     * Used by the Location-Tree to retrieve the Route to a clicked Object
     *
     * @param   mixed $p_object_id
     *
     * @return  string  Route as String in a comma separated format  exp: "2012,32"
     */
    public function get_node_hierarchy($p_object_id)
    {
        $l_path = [$p_object_id];
        $l_row = $this->get_data(null, $p_object_id)
            ->get_row();
        $check = null;

        if (isys_tenantsettings::get('cmdb.logical-location.object-type-filter', 1)) {
            $check = $l_row['isys_obj__isys_obj_type__id'] != defined_or_default('C__OBJTYPE__WORKSTATION');
        }

        if ($check === null && !isset(self::$objTypeIds[$this->get_category_const()])) {
            self::$objTypeIds[$this->get_category_const()] = $this->getObjTypeIdsByCategory('C__CATG__ASSIGNED_LOGICAL_UNIT');
            $check = !in_array($l_row['isys_obj__isys_obj_type__id'], self::$objTypeIds[$this->get_category_const()]);
        }

        if (!empty($l_row['isys_catg_logical_unit_list__isys_obj__id__parent']) && $check) {
            $l_path[] = $l_row['isys_catg_logical_unit_list__isys_obj__id__parent'];
        }

        return implode(",", $l_path);
    }

    /**
     * Retrieve logical physical path
     *
     * @param $p_obj
     *
     * @return array
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_logical_physical_path($p_obj)
    {
        $l_return = [];
        $l_parentid = $p_obj;
        $l_workstation_obj = null;

        $l_object_types_workstation = $this->get_object_types_by_category(defined_or_default('C__CATG__LOGICAL_UNIT'), 'g', false);
        $l_object_types_assigned_workstations = $this->get_object_types_by_category(defined_or_default('C__CATG__ASSIGNED_WORKSTATION'), 'g', false);
        $l_current_obj_type_id = $this->get_objTypeID($p_obj);

        if (in_array($l_current_obj_type_id, $l_object_types_workstation)) {
            // Add logical location
            $l_return[] = $l_parentid = $this->get_parent_by_id($p_obj);
        } elseif (in_array($l_current_obj_type_id, $l_object_types_assigned_workstations)) {
            $l_workstation_obj = $this->get_parent_by_id($p_obj);
            if ($l_workstation_obj) {
                if (($l_assigned_person = $this->get_parent_by_id($l_workstation_obj))) {
                    // Add workstation
                    $l_return[] = $l_workstation_obj;
                    // Add Person
                    $l_return[] = $l_parentid = $l_assigned_person;
                }
            }
        }

        $l_location_dao = isys_cmdb_dao_category_g_location::instance($this->get_database_component());

        while (($l_parentid = $l_location_dao->get_parent_id_by_object($l_parentid)) !== false) {
            if (in_array($l_parentid, $l_return)) {
                throw new RuntimeException(isys_application::instance()->container->get('language')
                        ->get('LC__CATG__LOCATION__RECURSION_IN_OBJECT') . ' #' . $l_parentid . ' "' . $this->get_obj_name_by_id_as_string($l_parentid) . '"');
            }

            if ($l_parentid != defined_or_default('C__OBJ__ROOT_LOCATION')) {
                $l_return[] = $l_parentid;
            }
        }

        return $l_return;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'parent'      => (new ObjectBrowserProperty(
                'C__CMDB__CATG__LOGICAL_UNIT__PARENT',
                'LC__CMDB__CATG__LOGICAL_UNIT__PARENT',
                'isys_catg_logical_unit_list__isys_obj__id__parent',
                'isys_catg_logical_unit_list',
                [],
                'C__CATG__PERSON_ASSIGNED_WORKSTATION'
            ))->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__LOGICAL_UNIT')
            )->setPropertyDataRelationHandler(
                new isys_callback(
                    [
                        'isys_cmdb_dao_category_g_logical_unit',
                        'callback_property_relation_handler'
                    ],
                    [
                        'isys_cmdb_dao_category_g_logical_unit',
                        true
                    ]
                )
            )->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                             FROM isys_catg_logical_unit_list
                               INNER JOIN isys_obj ON isys_obj__id = isys_catg_logical_unit_list__isys_obj__id__parent',
                    'isys_catg_logical_unit_list',
                    'isys_catg_logical_unit_list__id',
                    'isys_catg_logical_unit_list__isys_obj__id',
                    '',
                    '',
                    idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                        'isys_obj__isys_obj_type__id IN
                               (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
                                   INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                                   WHERE isysgui_catg__const = \'C__CATG__PERSON_ASSIGNED_WORKSTATION\')'
                    ])
                ),
                Property::C__PROPERTY__DATA__JOIN             => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_logical_unit_list', 'LEFT', 'isys_catg_logical_unit_list__isys_obj__id', 'isys_obj__id'),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_logical_unit_list__isys_obj__id__parent', 'isys_obj__id'),
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_logical_unit_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__LOGICAL_UNIT', 'C__CATG__LOGICAL_UNIT')
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
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status == isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_catg_logical_unit_list', $p_object_id);
            }
            if (($p_status == isys_import_handler_cmdb::C__CREATE || $p_status == isys_import_handler_cmdb::C__UPDATE) && $p_category_data['data_id'] > 0) {
                $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['parent'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL,
                    null,
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );

                return $p_category_data['data_id'];
            }
        }

        return false;
    }

    /**
     * See ID-4425
     * This method will be emitted after purging the entry in logical location.
     * If entry logical unit has been purged than check if the parent has a location.
     * If the parent has a location than change the location to the parent object.
     *
     * @param $p_dao         isys_cmdb_dao_category_g_logical_unit
     * @param $p_unused1     string
     * @param $p_unused2     int
     * @param $p_posts       array
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function signal_purge_entry($p_dao, $p_unused1, $p_unused2, $p_posts)
    {
        if (!isys_tenantsettings::get('cmdb.logical-location.handle-location-inheritage', false)) {
            return;
        }

        $catEntryID = $p_posts[0];
        $objectID = $p_dao->get_object_id();
        $objectTypeID = $p_dao->get_objTypeID($objectID);

        if ($p_dao->objtype_is_catg_assigned($objectTypeID, filter_defined_constants(['C__CATG__LOCATION', 'C__CATG__ASSIGNED_LOGICAL_UNIT']))) {
            $locationParentObjectID = $p_dao->retrieve('SELECT isys_catg_location_list__parentid FROM isys_catg_location_list 
                WHERE isys_catg_location_list__isys_obj__id = 
                (
                  SELECT isys_catg_logical_unit_list__isys_obj__id__parent FROM isys_catg_logical_unit_list 
                  WHERE isys_catg_logical_unit_list__id = ' . $p_dao->convert_sql_id($catEntryID) . '
                )')
                ->get_row_value('isys_catg_location_list__parentid');

            $locationEntry = $p_dao->retrieve('SELECT isys_catg_location_list__id, isys_catg_location_list__parentid FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' .
                $p_dao->convert_sql_id($objectID))
                ->get_row();

            $locationDao = isys_cmdb_dao_category_g_location::instance(isys_application::instance()->database);
            if ($locationParentObjectID > 0) {
                $locationDao->handle_location_inheritage(
                    true,
                    $objectTypeID,
                    $locationEntry['isys_catg_location_list__id'],
                    $objectID,
                    $locationParentObjectID,
                    $locationEntry['isys_catg_location_list__parentid']
                );
            }
        }
    }

    /**
     * isys_cmdb_dao_category_g_logical_unit constructor.
     *
     * @param isys_component_database $p_db
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_relation = [
            'field'     => 'parent',
            'direction' => C__RELATION_DIRECTION__I_DEPEND_ON,
            'type'      => defined_or_default('C__RELATION_TYPE__LOGICAL_UNIT')
        ];
        // Remove signal before connecting the signal otherwise the signal will be duplicated because in some cases classes will be reinitialized
        isys_component_signalcollection::get_instance()
            ->disconnect('mod.cmdb.beforeCategoryEntryRank', [
                'isys_cmdb_dao_category_g_logical_unit',
                'signal_purge_entry'
            ]);

        // Connect signal
        isys_component_signalcollection::get_instance()
            ->connect('mod.cmdb.beforeCategoryEntryRank', [
                'isys_cmdb_dao_category_g_logical_unit',
                'signal_purge_entry'
            ]);

        return parent::__construct($p_db);
    }
}
