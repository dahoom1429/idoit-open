<?php

/**
 * i-doit
 *
 * Auth: dao class for module dialog_admin
 *
 * @package     i-doit
 * @subpackage  dao
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_dao_dialog_admin extends isys_auth_module_dao
{
    protected function cleanup($p_method = null)
    {
        switch ($p_method) {
            case 'table':
                $this->cleanup_table();
                break;
            case 'custom':
                $this->cleanup_custom_table();
                break;
            default:
                $this->cleanup_table()
                    ->cleanup_custom_table();
                break;
        }

        return $this;
    }

    /**
     * Method for cleaning auth paths for Dialog Tables
     *
     * @return $this
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function cleanup_table()
    {
        // Prepare delete query
        $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';
        $l_delete_arr = [];

        $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth ' . 'WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id($this->m_module_id) . ' ' .
            'AND isys_auth__path LIKE \'TABLE/%\'';

        $l_res = $this->retrieve($l_auth_query);
        try {
            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_path_arr = explode('/', $l_row['isys_auth__path']);
                    if ($l_path_arr[1] == isys_auth::WILDCHAR) {
                        continue;
                    }

                    $l_indicator = strtolower($l_path_arr[1]);
                    $l_auth_id = $l_row['isys_auth__id'];

                    $l_check_query = 'SHOW TABLES LIKE ' . $this->convert_sql_text($l_indicator);
                    $l_res_check = $this->retrieve($l_check_query);
                    if ($l_res_check->num_rows() == 0) {
                        $l_delete_arr[] = $l_auth_id;
                    }
                }
                if (count($l_delete_arr) > 0) {
                    $l_delete_query .= '(' . implode(',', $l_delete_arr) . ')';
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
     * Method for cleaning auth paths for custom Dialog Tables
     *
     * @return $this
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function cleanup_custom_table()
    {
        // Prepare delete query
        $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';
        $l_delete_arr = [];

        $l_auth_query = 'SELECT isys_auth__id, isys_auth__path 
            FROM isys_auth 
            WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id($this->m_module_id) . '
            AND isys_auth__path LIKE \'CUSTOM/%\'';

        $l_res = $this->retrieve($l_auth_query);
        try {
            if ($l_res->num_rows() > 0) {
                $l_query = 'SELECT * FROM isysgui_catg_custom';

                $l_res_custom_tables = $this->retrieve($l_query);
                $l_custom_tables = [];

                while ($l_row_custom_tables = $l_res_custom_tables->get_row()) {
                    $l_data = unserialize($l_row_custom_tables['isysgui_catg_custom__config']);

                    if (is_array($l_data) && count($l_data)) {
                        foreach ($l_data AS $l_custom_category_content) {
                            if ($l_custom_category_content['popup'] != 'dialog_plus') {
                                continue;
                            }

                            $l_custom_tables[] = $l_custom_category_content['identifier'];
                        }
                    }
                }

                while ($l_row = $l_res->get_row()) {
                    $l_path_arr = explode('/', $l_row['isys_auth__path']);

                    if ($l_path_arr[1] == isys_auth::WILDCHAR) {
                        continue;
                    }

                    $l_indicator = strtolower($l_path_arr[1]);
                    $l_auth_id = $l_row['isys_auth__id'];

                    if (!in_array($l_indicator, $l_custom_tables)) {
                        $l_delete_arr[] = $l_auth_id;
                    }
                }

                if (count($l_delete_arr) > 0) {
                    $l_delete_query .= '(' . implode(',', $l_delete_arr) . ')';
                    $this->update($l_delete_query);
                    $this->apply_update();
                }
            }
        } catch (isys_exception_general $e) {
            throw new isys_exception_general($e->getMessage());
        }

        return $this;
    }
}