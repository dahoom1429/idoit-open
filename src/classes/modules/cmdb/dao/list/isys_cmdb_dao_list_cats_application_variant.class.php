<?php

/**
 * i-doit
 *
 * DAO: list for application variants
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_application_variant extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__APPLICATION_VARIANT');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_s_application_variant::instance($this->m_db)
            ->get_data(null, $p_objID, '', null, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_app_variant_list__title"   => "LC__CMDB__CATS__APPLICATION_VARIANT__TITLE",
            "isys_cats_app_variant_list__variant" => "LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT"
        ];
    }
}