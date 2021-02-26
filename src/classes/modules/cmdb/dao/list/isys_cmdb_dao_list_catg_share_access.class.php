<?php

/**
 * @package    i-doit
 * @subpackage
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_share_access extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__SHARE_ACCESS');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method which helps modifying each row.
     *
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_comp_database;

        if ($p_row['isys_catg_share_access_list__isys_catg_relation_list__id']) {
            $relationObjectStatus = $this->retrieve('SELECT isys_obj__status AS status FROM isys_catg_relation_list 
              INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id
              WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($p_row['isys_catg_share_access_list__isys_catg_relation_list__id']))->get_row_value('status');

            if ($relationObjectStatus != C__RECORD_STATUS__NORMAL) {
                $p_row['isys_catg_shares_list__title'] = isys_tenantsettings::get('gui.empty_value', '-');
            }
        }

        $p_row['object'] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($p_row['isys_connection__isys_obj__id'], isys_cmdb_dao::instance($g_comp_database)
                ->get_obj_name_by_id_as_string($p_row['isys_connection__isys_obj__id']), C__LINK__OBJECT);
    }

    /**
     * Method for retrieving the displayable fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_shares_list__title"            => "LC__CMDB__CATG__SHARES__SHARE_NAME",
            "object"                                  => "LC__POPUP__BROWSER__SELECTED_OBJECT",
            "isys_catg_share_access_list__mountpoint" => "LC__CMDB__CATG__SHARE_ACCESS__MOUNTPOINT"
        ];
    }
}