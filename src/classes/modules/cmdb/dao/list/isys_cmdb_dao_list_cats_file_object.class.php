<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for manuals
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org> - 2007-08-21
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_file_object extends isys_component_dao_category_table_list
{
    /**
     * Return category constant.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__FILE_OBJECTS');
    }

    /**
     * Return category type constant.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_general
     */
    public function get_result($p_table = null, $p_obj_id = null, $p_cRecStatus = null)
    {
        // Calculate record status
        $recordStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        // Prepare sql statement
        $sql = '
            SELECT  connectionList.connection as connectionId, 
                    fileObject.isys_obj__id, 
                    fileObject.isys_obj__title, 
                    targetObject.isys_obj__id as objectId, 
                    targetObject.isys_obj__title as objectTitle,
                    targetObjectType.isys_obj_type__title as objectTypeTitle,
                    connectionList.catId as categoryEntryId,
                    (SELECT count(*) FROM isys_catg_file_list WHERE isys_catg_file_list__isys_connection__id = connectionList.connection) AS fileCategory,
                    (SELECT count(*) FROM isys_catg_manual_list WHERE isys_catg_manual_list__isys_connection__id = connectionList.connection) AS manualCategory,
                    (SELECT count(*) FROM isys_catg_emergency_plan_list WHERE isys_catg_emergency_plan_list__isys_connection__id = connectionList.connection) AS emergencyPlanCategory
            FROM (
              # Select for file
              SELECT  fl.isys_catg_file_list__isys_connection__id AS connection, 
                      fl.isys_catg_file_list__isys_obj__id target,
                      fl.isys_catg_file_list__id catId
              FROM isys_catg_file_list fl WHERE fl.isys_catg_file_list__status = ' . $this->convert_sql_int($recordStatus) . '
              UNION
              # Select for manuals
              SELECT    ml.isys_catg_manual_list__isys_connection__id, 
                        ml.isys_catg_manual_list__isys_obj__id,
                        ml.isys_catg_manual_list__id
              FROM isys_catg_manual_list ml WHERE ml.isys_catg_manual_list__status = ' . $this->convert_sql_int($recordStatus) . '
              UNION
              # Select for emergency plan
              SELECT    epl.isys_catg_emergency_plan_list__isys_connection__id, 
                        epl.isys_catg_emergency_plan_list__isys_obj__id,
                        epl.isys_catg_emergency_plan_list__id
              FROM isys_catg_emergency_plan_list epl
              WHERE epl.isys_catg_emergency_plan_list__status = ' . $this->convert_sql_int($recordStatus) . ') connectionList
        INNER JOIN isys_connection con ON con.isys_connection__id = connectionList.connection
        INNER JOIN isys_obj fileObject ON con.isys_connection__isys_obj__id = fileObject.isys_obj__id
        INNER JOIN isys_obj targetObject ON connectionList.target = targetObject.isys_obj__id
        INNER JOIN isys_obj_type targetObjectType ON targetObject.isys_obj__isys_obj_type__id = targetObjectType.isys_obj_type__id
        WHERE fileObject.isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        // Retrieve and return...
        return $this->retrieve($sql);
    }

    /**
     *
     * @param  array &$row
     */
    public function modify_row(&$row)
    {
        // Prepare...
        $quickInfo = new isys_ajax_handler_quick_info();

        // Calculate targetd category
        $categoryConstant = defined_or_default('C__CATG__FILE');

        if (defined('C__CATG__MANUAL') && !!$row['manualCategory']) {
            $categoryConstant = C__CATG__MANUAL;
        } elseif (defined('C__CATG__EMERGENCY_PLAN') && !!$row['emergencyPlanCategory']) {
            $categoryConstant = defined_or_default('C__CATG__EMERGENCY_PLAN');
        }

        // Set category title
        $row['categoryTitle'] = isys_application::instance()->container->get('language')->get(
            isys_application::instance()->container->get('cmdb_dao')->get_catg_name_by_id_as_string($categoryConstant)
        );

        // Create quick info for object
        $row['objectTitle'] = $quickInfo->get_quick_info($row['objectId'], $row['objectTitle'], C__LINK__OBJECT);

        // Use connection id as identifier
        $row['isys_cats_file_list__id'] = $row['connectionId'];
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "objectTitle"     => "LC__CMDB__CATG__GLOBAL_TITLE",
            "objectTypeTitle" => "LC__CMDB__OBJTYPE",
            "categoryTitle"   => "LC__CMDB__CATG__CATEGORY"
        ];
    }
}
