<?php

/**
 * i-doit
 *
 * CMDB DAO: Global rearward category for VRRP.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_vrrp_view extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'vrrp_view';

    /**
     *
     * @param   integer $p_log_port_cat_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_vrrp_by_log_port($p_log_port_cat_id)
    {
        $l_sql = 'SELECT *
			FROM isys_catg_vrrp_member_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_vrrp_member_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_vrrp_member_list__isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port_cat_id) . '
			AND isys_catg_vrrp_member_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     *
     * @param   integer $p_obj_id
     *
     * @see     ID-2721
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_sub_sql = 'SELECT isys_catg_log_port_list__id
            FROM isys_catg_log_port_list
            WHERE isys_catg_log_port_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
            AND isys_catg_log_port_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        $l_sql = 'SELECT COUNT(*) AS count
			FROM isys_catg_vrrp_member_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_vrrp_member_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_vrrp_member_list__isys_catg_log_port_list__id IN (' . $l_sub_sql . ')
			AND isys_catg_vrrp_member_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql)
            ->get_row_value('count');
    }
}