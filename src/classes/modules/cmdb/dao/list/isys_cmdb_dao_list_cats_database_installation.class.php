<?php

/**
 * i-doit
 *
 * DAO: list for cluster members
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_database_installation extends isys_cmdb_dao_list_cats_application_assigned_obj
{
    /**
     * Return constant of category.
     *
     * @return  int|null
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__DATABASE_INSTALLATION');
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
     * @param  array &$row
     */
    public function modify_row(&$row)
    {
        parent::modify_row($row);

        /**
         * @var isys_ajax_handler_quick_info $quickInfo
         */
        $quickInfo = isys_factory::get_instance('isys_ajax_handler_quick_info');
        $dao = isys_cmdb_dao_category_s_database_installation::instance(isys_application::instance()->container->get('database'));

        // viewMode=1100&tvMode=1006&objID=189911&objTypeID=5&cateID=1&catgID=210
        // Create row pattern for row
        $rowLinkArr = [
            C__CMDB__GET__VIEWMODE   => 1100,
            C__CMDB__GET__TREEMODE   => defined_or_default('C__CMDB__VIEW__TREE_OBJECT'),
            C__CMDB__GET__CATG       => defined_or_default('C__CATG__DATABASE')
        ];

        $assignedDbs = $dao->getAssignedDatabases($row['isys_catg_application_list__id']);
        $row['assigned_databases'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (!empty($assignedDbs)) {
            $list = [];
            foreach ($assignedDbs as $id => $dbData) {
                $rowLinkArr[C__CMDB__GET__OBJECT] = $dbData[C__CMDB__GET__OBJECT];
                $rowLinkArr[C__CMDB__GET__OBJECTTYPE] = $dbData[C__CMDB__GET__OBJECTTYPE];
                $rowLinkArr[C__CMDB__GET__CATLEVEL] = $id;

                $list[] = $quickInfo->get_link('', $dbData['title'], isys_helper_link::create_url($rowLinkArr));
            }
            $row['assigned_databases'] = '<ul><li>' . implode('</li><li>', $list) . '</li></ul>';
        }

        $row['version_sort'] = preg_replace_callback(
            '/\d+/',
            static function ($matches) {
                return str_pad($matches[0], 6, '0', STR_PAD_LEFT);
            },
            $row['assigned_version']
        );
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'rel_obj_title'                       => 'LC__CATS__APPLICATION_ASSIGNMENT__INSTALLATION_INSTANCE',
            'main_obj_title'                      => 'LC__UNIVERSAL__INSTALLED_ON',
            'assigned_license'                    => 'LC__CMDB__CATG__LIC_ASSIGN__LICENSE',
            'assigned_version'                    => 'LC__CATG__VERSION_TITLE_AND_PATCHLEVEL',
            'isys_cats_app_variant_list__variant' => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
            'assigned_databases'                  => 'LC__CATS__DATABASE_INSTALLATION__ASSIGNED_DATABASES',
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
        if ($p_column == 'assigned_version') {
            $p_column = 'version_sort';
        }

        return $p_column . " " . $p_direction;
    }
}
