<?php

/**
 * i-doit
 *
 * Export helper for specific category database instance
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_specific_database_instance_helper extends isys_export_helper
{
    /**
     * @param $id
     *
     * @return isys_export_data
     */
    public function databaseSchema($id)
    {
        $arr = [];
        $dao = isys_cmdb_dao_category_s_database_schema::instance($this->m_database);

        $result = $dao->get_data(null, null, "AND isys_connection__isys_obj__id = " . $dao->convert_sql_id($id));

        while ($data = $result->get_row()) {

            $objectData = $dao->get_object_by_id($data['isys_cats_database_schema_list__isys_obj__id'])
                ->get_row();

            if ($objectData['isys_obj__status'] != C__RECORD_STATUS__NORMAL && $objectData['isys_obj__status'] !== null) {
                continue;
            }

            $arr[] = [
                'id'    => $objectData['isys_obj__id'],
                'type'  => $objectData['isys_obj_type__const'],
                'sysid' => $objectData['isys_obj__sysid'],
                'title' => $objectData['isys_obj__title']
            ];

        }

        return new isys_export_data($arr);
    }
    
    /**
     * Import method for retrieving a DB schema by DB object ID's.
     *
     * @param   array $value
     *
     * @return  array
     */
    public function databaseSchema_import($value)
    {
        return $this->get_object_id_from_member($value);
    }
}
