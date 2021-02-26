<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: person assigned workstation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_person_assigned_workstation extends isys_cmdb_dao_category_global
{
    /**
     * Categories name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_assigned_workstation';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Categories table. Will be used in the generic SQL statements.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_logical_unit_list';

    /**
     * This method is used by the object-browser to retrieve the selected object.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_selected_objects($p_obj_id)
    {
        return $this->get_data(null, $p_obj_id);
    }

    /**
     * Remove entry by parent object id and assigned object
     *
     * @param int $parentObjectId
     * @param int $assignedObjectId
     */
    public function deleteEntryByAssignedId($parentObjectId, $assignedObjectId)
    {
        $dao = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db);

        $data = $dao->get_data(
            null,
            $assignedObjectId,
            ' AND isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($parentObjectId)
        )->get_row();

        if (!empty($data)) {
            isys_cmdb_dao_category_g_relation::instance($this->m_db)
                ->delete_relation($data['isys_catg_logical_unit_list__isys_catg_relation_list__id']);
            $objectTypeID = $this->get_objTypeID($data['isys_catg_logical_unit_list__isys_obj__id']);

            // See ID-4425
            if ($this->objtype_is_catg_assigned($objectTypeID, filter_defined_constants(['C__CATG__LOCATION', 'C__CATG__ASSIGNED_LOGICAL_UNIT'])) &&
                isys_tenantsettings::get('cmdb.logical-location.handle-location-inheritage', false)) {
                $locationEntry = $this->retrieve('SELECT isys_catg_location_list__id, isys_catg_location_list__parentid FROM isys_catg_location_list 
                      WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($data['isys_catg_logical_unit_list__isys_obj__id']))
                    ->get_row();

                $locationParentObjectID = $this->retrieve('SELECT isys_catg_location_list__parentid FROM isys_catg_location_list 
                WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]))
                    ->get_row_value('isys_catg_location_list__parentid');

                isys_cmdb_dao_category_g_location::instance($this->m_db)
                    ->handle_location_inheritage(
                        true,
                        $objectTypeID,
                        $locationEntry['isys_catg_location_list__id'],
                        $data['isys_catg_logical_unit_list__isys_obj__id'],
                        $locationParentObjectID,
                        $locationEntry['isys_catg_location_list__parentid']
                    );
            }

            $this->delete_entry($data['isys_catg_logical_unit_list__id'], 'isys_catg_logical_unit_list');
        }
    }

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element(&$p_cat_id, &$p_intOldRecStatus, $p_create = false)
    {
        $l_dao = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db);
        $l_ids = isys_format_json::decode($_POST['C__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION__HIDDEN']);
        $l_check_array = (is_array($l_ids)) ? array_flip($l_ids) : [];

        // First we delete all connections and relations.
        $l_dao_res = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

        $locationParentObjectID = $this->retrieve('SELECT isys_catg_location_list__parentid FROM isys_catg_location_list 
                WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]))
            ->get_row_value('isys_catg_location_list__parentid');

        if ($l_dao_res->num_rows() > 0) {
            $locationDao = isys_cmdb_dao_category_g_location::instance($this->m_db);

            while ($l_dao_row = $l_dao_res->get_row()) {
                if (count($l_check_array) && isset($l_check_array[$l_dao_row['isys_catg_logical_unit_list__isys_obj__id']])) {
                    unset($l_check_array[$l_dao_row['isys_catg_logical_unit_list__isys_obj__id']]);
                    continue;
                }

                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->m_db);
                $l_relation_dao->delete_relation($l_dao_row['isys_catg_logical_unit_list__isys_catg_relation_list__id']);
                $objectTypeID = $this->get_objTypeID($l_dao_row['isys_catg_logical_unit_list__isys_obj__id']);

                // See ID-4425
                if ($locationParentObjectID > 0 && $this->objtype_is_catg_assigned($objectTypeID, filter_defined_constants(['C__CATG__LOCATION', 'C__CATG__ASSIGNED_LOGICAL_UNIT'])) &&
                    isys_tenantsettings::get('cmdb.logical-location.handle-location-inheritage', false)) {
                    $locationEntry = $this->retrieve('SELECT isys_catg_location_list__id, isys_catg_location_list__parentid FROM isys_catg_location_list 
                      WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($l_dao_row['isys_catg_logical_unit_list__isys_obj__id']))
                        ->get_row();

                    $locationDao->handle_location_inheritage(
                        true,
                        $objectTypeID,
                        $locationEntry['isys_catg_location_list__id'],
                        $l_dao_row['isys_catg_logical_unit_list__isys_obj__id'],
                        $locationParentObjectID,
                        $locationEntry['isys_catg_location_list__parentid']
                    );
                }

                $this->delete_entry($l_dao_row['isys_catg_logical_unit_list__id'], 'isys_catg_logical_unit_list');
            }
        }

        // For each selected ID we create/save the connections.
        if (is_array($l_check_array) && count($l_check_array) > 0) {
            foreach ($l_check_array as $l_id => $l_index) {
                $l_relation_id = null;
                $l_data = $l_dao->get_data_by_object($l_id)
                    ->get_row();

                if (isset($l_data['isys_catg_logical_unit_list__id'])) {
                    $l_cat_id = $l_data['isys_catg_logical_unit_list__id'];
                    $l_relation_id = $l_data['isys_catg_logical_unit_list__isys_catg_relation_list__id'];
                } else {
                    $l_cat_id = $this->create_connector('isys_catg_logical_unit_list', $l_id);
                }

                $l_dao->save(
                    $l_cat_id,
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $l_id,
                    $l_relation_id,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );
            }
        }
    }

    /**
     * Checks if assignment already exists
     *
     * @param int $p_parent
     * @param int $p_assigned_workstation
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function assignment_exists($p_parent, $p_assigned_workstation)
    {
        $l_sql = 'SELECT * FROM isys_catg_logical_unit_list ' . 'WHERE isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($p_parent) . ' ' .
            'AND isys_catg_logical_unit_list__isys_obj__id = ' . $this->convert_sql_id($p_assigned_workstation);
        $l_res = $this->retrieve($l_sql);

        return ($l_res->num_rows() > 0);
    }

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = 'SELECT count(isys_catg_logical_unit_list__id) AS count ' . 'FROM isys_catg_logical_unit_list ' . 'WHERE TRUE ';

        if ($l_obj_id !== null) {
            $l_sql .= ' AND isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($l_obj_id) . ' ';
        }

        $l_sql .= ' AND isys_catg_logical_unit_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return (int)$l_data["count"];
    }

    /**
     * Get data method, uses logical unit DAO.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_db);

        return $l_dao->get_data_by_parent($p_obj_id);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_workstations' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Parent object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_logical_unit_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_logical_unit_list
                                INNER JOIN isys_obj ON isys_obj__id = isys_catg_logical_unit_list__isys_obj__id',
                        'isys_catg_logical_unit_list',
                        'isys_catg_logical_unit_list__id',
                        'isys_catg_logical_unit_list__isys_obj__id__parent',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            'isys_obj__isys_obj_type__id IN
                                (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
                                    INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                                    WHERE isysgui_catg__const = \'C__CATG__LOGICAL_UNIT\')'
                        ]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_logical_unit_list__isys_obj__id__parent'])
                    ),
                    C__PROPERTY__DATA__JOIN => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_logical_unit_list',
                            'LEFT',
                            'isys_catg_logical_unit_list__isys_obj__id__parent',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_logical_unit_list__isys_obj__id',
                            'isys_obj__id',
                            'main',
                            'parent',
                            'parent'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__MULTISELECTION => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__LOGICAL_UNIT'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'description'           => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_logical_unit_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__PERSON_ASSIGNED_WORKSTATION', 'C__CATG__PERSON_ASSIGNED_WORKSTATION')
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
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            /**
             * @var  $l_dao  isys_cmdb_dao_category_g_logical_unit
             */
            $l_dao = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db);
            // Check if connection already exists
            $l_exists = $this->assignment_exists($p_object_id, $p_category_data['properties']['assigned_workstations'][C__DATA__VALUE]);
            if ($p_status == isys_import_handler_cmdb::C__CREATE || !$l_exists) {
                $p_category_data['data_id'] = $this->create_connector('isys_catg_logical_unit_list', $p_category_data['properties']['assigned_workstations'][C__DATA__VALUE]);
            }
            if ($p_status == isys_import_handler_cmdb::C__CREATE || $p_status == isys_import_handler_cmdb::C__UPDATE) {
                $l_indicator = $l_dao->save(
                    $p_category_data['data_id'],
                    $p_object_id,
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['assigned_workstations'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
