<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for CPU.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_memory extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__MEMORY');
    }

    /**
     * Return constant of category type
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Modify row.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $p_arrRow["isys_catg_memory_list__capacity"] = isys_convert::memory($p_arrRow["isys_catg_memory_list__capacity"], $p_arrRow["isys_memory_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD);

        $p_arrRow["isys_catg_memory_list__capacity"] = isys_convert::formatNumber($p_arrRow["isys_catg_memory_list__capacity"]) . " " . $p_arrRow["isys_memory_unit__title"];
    }

    /**
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            "isys_memory_title__title"              => "LC__CMDB__CATG__TITLE",
            "isys_memory_manufacturer__title"       => "LC__CMDB__CATG__MANUFACTURER",
            "isys_catg_memory_list__capacity"       => "LC__CMDB_CATG__MEMORY_CAPACITY",
            "isys_catg_memory_list__description"    => 'LC__CMDB_CATG__MEMORY_DESCRIPTION',
            "isys_memory_type__title"               => 'LC__CMDB_CATG__MEMORY_TYPE',
        ];
    }
}