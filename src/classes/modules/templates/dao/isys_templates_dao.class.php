<?php

/**
 * i-doit
 *
 * Template Module Dao
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_templates_dao extends isys_module_dao
{
    /**
     * Retrieves all templates.
     *
     * @param  integer $objectId
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_templates($objectId = null)
    {
        return $this->get_data($objectId);
    }

    /**
     * Retrieve all mass change templates.
     *
     * @param  integer $objectId
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_mass_change_templates($objectId = null)
    {
        return $this->get_data($objectId, null, C__RECORD_STATUS__MASS_CHANGES_TEMPLATE);
    }

    /**
     * Retrieves all templates.
     *
     * @param  integer $objectId
     * @param  string  $orderBy
     * @param  integer $status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($objectId = null, $orderBy = null, $status = null)
    {
        $objectCondition = '';

        if ($objectId !== null) {
            $objectCondition = 'AND isys_obj__id = ' . $this->convert_sql_id($objectId);
        }

        $l_sql = 'SELECT *, isys_obj__id AS isys_id 
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE TRUE ' . $objectCondition . ' 
            AND isys_obj__status = ' . $this->convert_sql_int($status ?: C__RECORD_STATUS__TEMPLATE) . '
            GROUP BY isys_obj__id
            ORDER BY ' . ($orderBy ?: 'isys_obj_type__id, isys_obj__title');

        return $this->retrieve($l_sql);
    }

    /**
     * Deletes a template.
     *
     * @deprecated Simply use "delete_object".
     *
     * @param integer $objectId
     */
    public function delete_template($objectId)
    {
        isys_cmdb_dao::instance($this->m_db)
            ->delete_object($objectId);
    }
}
