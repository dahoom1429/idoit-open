<?php

/**
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_sound extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__SOUND');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_sound_manufacturer__title" => "LC__CMDB__CATG__MANUFACTURER",
            "isys_catg_sound_list__title"    => "LC__CMDB__CATG__TITLE"
        ];
    }
}