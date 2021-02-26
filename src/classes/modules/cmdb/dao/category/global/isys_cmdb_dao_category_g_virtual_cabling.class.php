<?php

/**
 * i-doit
 *
 * DAO: global category for virtual cabling
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_virtual_cabling extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_cabling';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__CABLING';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__CABLING;

    /**
     *
     * @param integer $p_obj_id
     *
     * @return integer
     */
    public function get_count($p_obj_id = null)
    {
        return isys_cmdb_dao_category_g_connector::instance($this->get_database_component())
            ->get_count($p_obj_id);
    }

    /**
     * Get data method.
     *
     * @todo  Remove this if possible!
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     * @param   string  $p_sort_by
     * @param   string  $p_direction
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null, $p_sort_by = null, $p_direction = null)
    {
        return isys_cmdb_dao_category_g_connector::instance($this->m_db)
            ->get_data($p_catg_list_id, $p_obj_id, $p_condition, $p_filter, $p_status, $p_sort_by, $p_direction);
    }
}