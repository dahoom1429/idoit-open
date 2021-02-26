<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_database_sa extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  int|null
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__DATABASE_SA');
    }

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method for modifying a certain row.
     *
     * @param  array $row
     *
     * @see    isys_component_dao_object_table_list::modify_row()
     */
    public function modify_row(&$row)
    {
        // Initial Load of table isys_memory_unit
        $dialogDao = isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', isys_application::instance()->container->get('database'));
        $sizeUnit = $dialogDao->get_data($row["isys_catg_database_sa_list__size_unit"]);
        $maxSizeUnit = $dialogDao->get_data($row["isys_catg_database_sa_list__max_size_unit"]);

        $size = isys_convert::memory(
            $row["isys_catg_database_sa_list__size"],
            $sizeUnit["isys_memory_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $maxSize = isys_convert::memory(
            $row["isys_catg_database_sa_list__max_size"],
            $maxSizeUnit["isys_memory_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $row["size"] = isys_convert::formatNumber($size) . " " . $sizeUnit["isys_memory_unit__title"];
        $row["max_size"] = isys_convert::formatNumber($maxSize) . " " . $maxSizeUnit["isys_memory_unit__title"];
    }

    /**
     * Method for retrieving the fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'assigned_dbms' => 'LC__CATG__DATABASE_SA__ASSIGNED_DBMS',
            'isys_catg_database_sa_list__title' => 'LC__CATG__DATABASE_TABLE__TITLE',
            'size' => 'LC__CATG__DATABASE_TABLE__SIZE',
            'max_size' => 'LC__CATG__DATABASE_TABLE__MAX_SIZE',
        ];
    }
}
