<?php

/**
 * i-doit
 * Auth: dao class for module cmdb
 *
 * @package     i-doit
 * @subpackage  dao
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_dao_cmdb extends isys_auth_module_dao
{
    /**
     * Determines which cleanup method should be called
     *
     * @param null $p_method
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function cleanup($p_method = null)
    {
        switch ($p_method) {
            case 'obj_id':
            case 'location':
                $this->cleanup_default($p_method, 'isys_obj', 'isys_obj__id');
                break;
            case 'obj_type':
            case 'obj_in_type':
                $this->cleanup_default($p_method, 'isys_obj_type', 'isys_obj_type__const');
                break;
            case 'category':
                $this->cleanup_category();
                break;
            default:

                $this->cleanup_default('obj_id', 'isys_obj', 'isys_obj__id')
                    ->cleanup_default('location', 'isys_obj', 'isys_obj__id')
                    ->cleanup_default('obj_type', 'isys_obj_type', 'isys_obj_type__const')
                    ->cleanup_default('obj_in_type', 'isys_obj_type', 'isys_obj_type__const')
                    ->cleanup_category();

                break;
        }

        return $this;
    }

    /**
     * Method for cleaning the auth paths for categories.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function cleanup_category()
    {
        // Prepare delete query
        $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';
        $l_delete_arr = [];

        // Get paths
        $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth
			WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id($this->m_module_id) . '
			AND isys_auth__path LIKE "CATEGORY/%";';

        try {
            $l_res = $this->retrieve($l_auth_query);

            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_path_arr = explode('/', $l_row['isys_auth__path']);
                    if ($l_path_arr[1] == isys_auth::WILDCHAR) {
                        continue;
                    }

                    $l_category_const = $l_path_arr[1];
                    $l_auth_id = $l_row['isys_auth__id'];

                    $l_check_query = 'SELECT isysgui_catg__id AS id, "g" AS type FROM isysgui_catg WHERE isysgui_catg__const = ' . $this->convert_sql_text($l_category_const) . '
						UNION
						SELECT isysgui_cats__id AS id, "s" AS type FROM isysgui_cats WHERE isysgui_cats__const = ' . $this->convert_sql_text($l_category_const) . '
						UNION
						SELECT isysgui_catg_custom__id AS id, "g" AS type FROM isysgui_catg_custom WHERE isysgui_catg_custom__const = ' .
                        $this->convert_sql_text($l_category_const);

                    $l_res_check = $this->retrieve($l_check_query);

                    if ($l_res_check->num_rows() == 0) {
                        $l_delete_arr[] = $l_auth_id;
                    }
                }

                if (count($l_delete_arr) > 0) {
                    $l_delete_query = $l_delete_query . '(' . implode(',', $l_delete_arr) . ')';
                    $this->update($l_delete_query);
                    $this->apply_update();
                }
            }
        } catch (isys_exception_general $e) {
            throw new isys_exception_general($e->getMessage());
        }

        return $this;
    }

    /**
     * Authentication rule handling for
     * object that gets ranked
     *
     * This function is registered via SignalSlot
     * and will be invoked after objects are ranked.
     * Whenever an object gets purged all related
     * rights will be deleted.
     *
     * @param isys_cmdb_dao $cmdbDao
     * @param integer       $direction
     * @param array         $objectIds
     *
     * @return void
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function objectRelatedAuthenticationRules(isys_cmdb_dao $cmdbDao, $direction, array $objectIds) {
        // Ensure we have a set of objectIds
        if (count($objectIds) > 0) {
            // Amount of affected rows
            $affectedRows = 0;

            // Iterate over all objects
            foreach ($objectIds AS $objectId) {
                try {
                    // Check whether object still exists
                    if ($cmdbDao->obj_exists($objectId) === false) {
                        // Object was removed from database - Build SQL for deleting corresponding auth rules
                        $deleteSql = 'DELETE FROM isys_auth
                                WHERE isys_auth__path    = '. $cmdbDao->convert_sql_text('OBJ_ID/' . $objectId) .' OR
                                      isys_auth__path LIKE '. $cmdbDao->convert_sql_text('CATEGORY_IN_OBJECT%+['. $objectId .']') .'
                               ';

                        // Update and save amount of affected rows
                        $cmdbDao->update($deleteSql);
                        $affectedRows += $cmdbDao->affected_after_update();
                        $cmdbDao->apply_update();

                        /**
                         * Handling auth rules of type `CATEGORY_IN_OBJECT/*[ID1,ID2... , $objectId, ...IDn]`
                         *
                         *  Three cases to handle:  [$objectId,...]
                         *                          [...,$objectId,...]
                         *                          [...,$objectId]
                         *
                         * This query will replace provided objectId everywhere in auth rule
                         */
                        $updateSql = 'UPDATE isys_auth 
                                      SET isys_auth__path = REPLACE(
                                        REPLACE(
                                          REPLACE(isys_auth__path, 
                                                  '. $cmdbDao->convert_sql_text('[' . $objectId . ',') .', 
                                                  '. $cmdbDao->convert_sql_text('[') .'
                                          ), 
                                          '. $cmdbDao->convert_sql_text(',' . $objectId . ',') .', 
                                          '. $cmdbDao->convert_sql_text(',') .'
                                        ), 
                                        '. $cmdbDao->convert_sql_text(',' . $objectId . ']') . ', 
                                        ' . $cmdbDao->convert_sql_text(']') . ')
                                      WHERE isys_auth__path LIKE '. $cmdbDao->convert_sql_text('CATEGORY_IN_OBJECT%+[%' . $objectId . '%]') .'
                                     ';

                        // Update and save amount of affected rows
                        $cmdbDao->update($updateSql);
                        $affectedRows += $cmdbDao->affected_after_update();
                        $cmdbDao->apply_update();
                    }
                } catch (Exception $e) {
                    /**
                     * No possibility to handle any exceptions here
                     */
                }
            }

            // Check whether any rows were affected by done database operations
            if ($affectedRows > 0) {
                // Invalidate auth cache
                isys_auth::invalidateCache();
            }
        }
    }
}