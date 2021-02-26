<?php

/**
 * i-doit
 *
 * DAO: global category list for versions.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_version extends isys_component_dao_category_table_list
{
    public function modify_row(&$p_row)
    {
        $p_row['version_sort'] = preg_replace_callback(
            '/\d+/',
            static function ($matches) {
                return str_pad($matches[0], 6, '0', STR_PAD_LEFT);
            },
            $p_row['isys_catg_version_list__title']
        );
    }

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_version_list__title'       => 'LC__CATG__VERSION_TITLE',
            'isys_catg_version_list__servicepack' => 'LC__CATG__VERSION_SERVICEPACK',
            'isys_catg_version_list__kernel'      => 'LC__CATG__VERSION_KERNEL',
            'isys_catg_version_list__hotfix'      => 'LC__CATG__VERSION_PATCHLEVEL',
            'version_sort'                        => false
        ];
    }

    /**
     * Order condition
     *
     * @param string $p_column
     * @param string $p_direction
     *
     * @return string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        if ($p_column == 'isys_catg_version_list__title') {
            $p_column = 'version_sort';
        }

        return $p_column . " " . $p_direction;
    }
}
