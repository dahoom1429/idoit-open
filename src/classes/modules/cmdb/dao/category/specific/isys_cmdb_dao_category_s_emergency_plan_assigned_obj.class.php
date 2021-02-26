<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * i-doit
 *
 * DAO: Specific category for emergency plans with assigned objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_emergency_plan_assigned_obj extends isys_cmdb_dao_category_s_emergency_plan implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'emergency_plan_assigned_obj';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS;

    /**
     * @var string
     */
    protected $m_entry_identifier = 'object';

    /**
     * Category's table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_emergency_plan_list';

    /**
     * Category's template.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_tpl = 'object_table_list.tpl';

    /**
     * @param int $objID
     * @param int $status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_assigned_objects($objID, $status = C__RECORD_STATUS__NORMAL)
    {
        $l_query = 'SELECT * FROM isys_catg_emergency_plan_list ' .
            'INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id ' .
            'LEFT JOIN isys_obj ON isys_catg_emergency_plan_list__isys_obj__id = isys_obj__id ' .
            'LEFT JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ' .
            'WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($objID) . ' AND isys_catg_emergency_plan_list__status = ' . $this->convert_sql_int($status);

        return $this->retrieve($l_query);
    }

    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_catg_emergency_plan_list__id) AS count FROM isys_catg_emergency_plan_list " .
            "INNER JOIN isys_connection ON isys_catg_emergency_plan_list__isys_connection__id = isys_connection__id " . "WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= " AND (isys_connection__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_catg_emergency_plan_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

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
     * @param   fixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = " ";

        if ($p_obj_id !== null) {
            $l_sql .= "AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int($p_status) . " ";
        }

        return isys_cmdb_dao_category_g_emergency_plan::instance($this->m_db)
            ->get_data($p_catg_list_id, null, $l_sql . $p_condition);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        $query = 'SELECT CONCAT(isys_obj_type__title, \' > \', isys_obj__title, \' {\', isys_obj__id, \'}\')
            FROM isys_catg_emergency_plan_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_emergency_plan_list__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id';

        return [
            'object' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CMDB__CATS__EMERGENCY_PLAN_ASSIGNED_OBJ__OBJECT',
                'LC__UNIVERSAL__TITLE',
                'isys_catg_emergency_plan_list__isys_obj__id',
                'isys_catg_emergency_plan_list'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                    $query,
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                )
            ])
        ];
    }

    /**
     * @param int   $objectID
     * @param array $objects
     *
     * @return void
     * @throws isys_exception_dao
     */
    public function attachObjects($objectID, array $objects)
    {
        $assignedObjects = $this->get_assigned_objects($objectID);
        $entries = [];

        while ($row = $assignedObjects->get_row()) {
            $entries[$row['isys_catg_emergency_plan_list__isys_obj__id']] = $row;
        }

        foreach ($objects as $object) {
            if (is_numeric($object)) {
                if (!isset($entries[$object])) {
                    $this->create($object, $objectID, C__RECORD_STATUS__NORMAL);
                } else {
                    unset($entries[$object]);
                }
            }
        }

        if (count($entries)) {
            $connectionDao = isys_cmdb_dao_connection::instance($this->m_db);
            $relationDao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            foreach ($entries as $object) {
                $connectionDao->delete($object['isys_connection__id']);
                $relationDao->delete_relation($object['isys_catg_emergency_plan_list__isys_catg_relation_list__id']);
            }
        }
    }

    /**
     * @param int      $objectID
     * @param int|null $connectedObjID
     * @param int      $newRecStatus
     *
     * @return false|int|mixed
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function create($objectID, $connectedObjID = null, $newRecStatus = C__RECORD_STATUS__NORMAL)
    {
        $l_connection_dao = new isys_cmdb_dao_connection($this->m_db);

        $sql = 'INSERT INTO isys_catg_emergency_plan_list SET ' .
            'isys_catg_emergency_plan_list__isys_obj__id = ' . $this->convert_sql_id($objectID) . ', ' .
            'isys_catg_emergency_plan_list__status = ' . $this->convert_sql_id($newRecStatus) . ', ' .
            'isys_catg_emergency_plan_list__isys_connection__id = ' . ($connectedObjID > 0 ? $this->convert_sql_id($l_connection_dao->add_connection($connectedObjID)) : null);

        if ($this->update($sql) && $this->apply_update()) {
            $categoryEntryId = $this->get_last_insert_id();

            // Create connection if necessary
            if ($connectedObjID > 0) {
                isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                    ->handle_relation($categoryEntryId, 'isys_catg_emergency_plan_list', defined_or_default('C__RELATION_TYPE__EMERGENCY_PLAN'), null, $objectID, $connectedObjID);
            }

            return $categoryEntryId;
        }
        return false;
    }

    /**
     * @param int    $p_object_id
     * @param int    $p_direction
     * @param string $p_table
     * @param null   $p_checkMethod
     * @param false  $p_purge
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function rank_record($p_object_id, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        $newStatus = null;
        $l_dao = new isys_cmdb_dao_category_g_emergency_plan($this->m_db);

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                $relationDao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
                $connectionDao = isys_cmdb_dao_connection::instance($this->m_db);
                $l_catdata = $l_dao->get_data($p_object_id)->get_row();
                if ($relationDao->delete_relation($l_catdata['isys_catg_emergency_plan_list__isys_catg_relation_list__id'])) {
                    $connectionDao->delete($l_catdata['isys_connection__id']);
                    $l_dao->delete_entry($p_object_id, 'isys_catg_emergency_plan_list');
                }
                return true;
            case C__NAVMODE__ARCHIVE:
                //set status
                $newStatus = C__RECORD_STATUS__ARCHIVED;
                break;
            case C__NAVMODE__RECYCLE:
                //set status
                $newStatus = C__RECORD_STATUS__NORMAL;
                break;
            case C__NAVMODE__DELETE:
                //mark as deleted
                $newStatus = C__RECORD_STATUS__DELETED;
                break;
        }

        if ($newStatus !== null) {
            $this->set_status($p_object_id, $_GET[C__CMDB__GET__OBJECT], $newStatus);
            return true;
        }
    }

    /**
     *
     * @param integer $p_cat_id
     * @param null    $p_obj_id
     * @param integer $p_status
     *
     * @throws isys_exception_dao
     */
    private function set_status($p_cat_id, $p_obj_id = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_update = "UPDATE isys_catg_emergency_plan_list " . "SET isys_catg_emergency_plan_list__status = " . $p_status . " " . "WHERE isys_catg_emergency_plan_list__id = " .
            $this->convert_sql_id($p_cat_id);

        $this->update($l_update);

        return $this->apply_update();
    }
}
