<?php

/**
 * i-doit
 *
 * DAO: global category for cable connection.
 *
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @copyright     synetics GmbH
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cable_connection extends isys_cmdb_dao_category_global
{
    /**
     * @var  string
     */
    protected $m_category = 'cable_connection';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG__CABLE_CONNECTION';

    /**
     * Use virtual table
     *
     * @var    string
     */
    protected $m_table = 'isys_catg_virtual_list';

    /**
     * Get-Count method for highlighting the category.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_sql = "SELECT
                        isys_cable_connection__id
                    FROM isys_catg_connector_list
                    INNER JOIN isys_cable_connection ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
                    INNER JOIN isys_obj ON isys_obj__id = isys_catg_connector_list__isys_obj__id
                    WHERE isys_cable_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ' LIMIT 1';

        return $this->retrieve($l_sql)
            ->count();
    }
}