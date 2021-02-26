<?php

/**
 * i-doit
 *
 * DAO: Category list for backup servers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_backup_assigned_objects extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__BACKUP__ASSIGNED_OBJECTS');
    }

    /**
     * Return constant of category type.
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Modify row method.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        if ($p_arrRow["isys_obj__id"] != null) {
            $l_quickinfo = new isys_ajax_handler_quick_info();

            $l_link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT     => $p_arrRow["isys_obj__id"],
                C__CMDB__GET__OBJECTTYPE => $p_arrRow["isys_obj__isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => defined_or_default('C__CATG__BACKUP'),
                C__CMDB__GET__TREEMODE   => $_GET["tvMode"]
            ]);

            $p_arrRow["isys_obj__title"] = $l_quickinfo->get_quick_info($p_arrRow["isys_obj__id"], $p_arrRow["isys_obj__title"], $l_link);
        }
    }

    /**
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_backup_list__title' => 'LC__CMDB__CATG__BACKUP__TITLE',
            'isys_obj__title'              => 'LC__CMDB__CATG__BACKUP__BACKUPS'
        ];
    }
}