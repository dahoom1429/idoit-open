<?php

/**
 * i-doit
 *
 * Files
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Dennis Stuecken <dstuecken@synetics.de
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_file extends isys_component_dao
{
    /**
     * Method for receiving all active file versions.
     *
     * @param  boolean $p_group_cat
     * @param  integer $p_limit
     * @param  integer $p_objid
     * @param  string  $p_order_by
     * @param  integer $l_nRecStat
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     * @author Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_active_file_versions($p_group_cat = true, $p_limit = null, $p_objid = null, $p_order_by = null, $l_nRecStat = null)
    {
        if ($l_nRecStat === null) {
            $l_nRecStat = C__RECORD_STATUS__NORMAL;
        }

        $l_sql = 'SELECT *, DATE_FORMAT(isys_file_physical__date_uploaded, "%d.%m.%Y") AS isys_date_up ';

        if ($p_group_cat === true) {
            $l_sql .= ', COUNT(isys_file_category__title) as isys_cat_sum ';
        }

        $l_sql .= 'FROM isys_cats_file_list list
			LEFT OUTER JOIN isys_file_category cat ON cat.isys_file_category__id = list.isys_cats_file_list__isys_file_category__id
			INNER JOIN isys_file_version version ON version.isys_file_version__id = list.isys_cats_file_list__isys_file_version__id
			INNER JOIN isys_file_physical physical ON physical.isys_file_physical__id = version.isys_file_version__isys_file_physical__id
			LEFT OUTER JOIN isys_obj ON isys_cats_file_list__isys_obj__id = isys_obj__id
			WHERE isys_cats_file_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';

        if ($p_objid !== null) {
            $l_sql .= 'AND isys_cats_distributor__isys_obj__id = ' . $this->convert_sql_id($p_objid) . ' ';
        }

        $l_sql .= 'AND isys_obj__status = ' . $this->convert_sql_int($l_nRecStat) . ' ';

        if ($p_group_cat === true) {
            $l_sql .= 'GROUP BY isys_cats_file_list__id ';
        }

        if ($p_order_by !== null) {
            $l_sql .= 'ORDER BY ' . $p_order_by . ' ';
        } else {
            $l_sql .= 'ORDER BY isys_file_version__id ASC ';
        }

        if ($p_limit !== null) {
            $l_sql .= 'LIMIT ' . $p_limit;
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for receiving all files inside the browser.
     *
     * @param   integer $p_objid
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_files($p_objid = null)
    {
        $l_sql = 'SELECT * FROM isys_file_version
			INNER JOIN isys_file_physical physical ON (physical.isys_file_physical__id = isys_file_version__isys_file_physical__id)
			WHERE TRUE ';

        if ($p_objid !== null) {
            $l_sql .= 'AND isys_file_version__isys_obj__id = ' . $this->convert_sql_id($p_objid) . ' ';
        }

        $l_sql .= 'ORDER BY isys_file_version__revision DESC;';

        return $this->retrieve($l_sql);
    }

    /**
     * Method for retrieving the filename, by a given file-ID.
     *
     * @param   integer $p_file_id
     *
     * @return  array
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_filename_as_string($p_file_id)
    {
        $l_sqlstr = 'SELECT isys_cats_file_list__filename
			FROM isys_cats_file_list
			WHERE isys_cats_file_list__id = ' . $this->convert_sql_id($p_file_id) . ';';

        return $this->retrieve($l_sqlstr)
            ->get_row();
    }
}

?>