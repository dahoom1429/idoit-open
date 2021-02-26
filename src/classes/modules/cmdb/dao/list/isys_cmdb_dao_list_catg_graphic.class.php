<?php

/**
 * @package     i-doit
 * @subpackage
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_graphic extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Retrieves the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__GRAPHIC');
    }

    /**
     * Retrieves the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method for modifying single field contents before rendering.
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row['concat_memory'] = isys_convert::memory(
            $p_row['isys_catg_graphic_list__memory'],
            $p_row['isys_catg_graphic_list__isys_memory_unit__id'],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $p_row['concat_memory'] = isys_convert::formatNumber($p_row['concat_memory']) . ' ' . $p_row['isys_memory_unit__title'];
    }

    /**
     * Retrieve an array of fields to display.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_graphic_manufacturer__title' => 'LC__CMDB__CATG__MANUFACTURER',
            'isys_catg_graphic_list__title'    => 'LC__CMDB__CATG__TITLE',
            'concat_memory'                    => 'LC__CMDB__CATG__MEMORY',
            'isys_catg_graphic_list__firmware' => 'LC__CMDB__CATG__GRAPHIC_FIRMWARE'
        ];
    }
}
