<?php

/**
 * i-doit
 *
 * DAO: AP List
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_access_point extends isys_component_dao_category_table_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__ACCESS_POINT');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = "SELECT * FROM isys_cats_access_point_list
			LEFT OUTER JOIN isys_wlan_channel ON isys_cats_access_point_list__isys_wlan_channel__id = isys_wlan_channel__id
			LEFT OUTER JOIN isys_wlan_auth ON isys_cats_access_point_list__isys_wlan_auth__id = isys_wlan_auth__id
			LEFT OUTER JOIN isys_wlan_function ON isys_cats_access_point_list__isys_wlan_function__id = isys_wlan_function__id
			LEFT OUTER JOIN isys_wlan_encryption ON isys_cats_access_point_list__encryption = isys_wlan_encryption__id
			WHERE isys_cats_access_point_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_cats_access_point_list__status = " . $this->convert_sql_int(empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus) . ";";

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param  array $p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        if ($p_arrRow["isys_cats_access_point_list__broadcast_ssid"] == 1) {
            $p_arrRow["isys_cats_access_point_list__broadcast_ssid"] = isys_application::instance()->container->get('language')
                ->get("LC__UNIVERSAL__YES");
        } else {
            $p_arrRow["isys_cats_access_point_list__broadcast_ssid"] = isys_application::instance()->container->get('language')
                ->get("LC__UNIVERSAL__NO");
        }

        if ($p_arrRow["isys_cats_access_point_list__mac_filter"] == 1) {
            $p_arrRow["isys_cats_access_point_list__mac_filter"] = isys_application::instance()->container->get('language')
                ->get("LC__UNIVERSAL__YES");
        } else {
            $p_arrRow["isys_cats_access_point_list__mac_filter"] = isys_application::instance()->container->get('language')
                ->get("LC__UNIVERSAL__NO");
        }
    }

    /**
     * Build header for the list.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_access_point_list__title"          => "LC__UNIVERSAL__TITLE",
            "isys_wlan_function__title"                   => "LC__CMDB__CATS__ACCESS_POINT_FUNCTION",
            "isys_cats_access_point_list__ssid"           => "LC__CMDB__CATS__ACCESS_POINT_SSID",
            "isys_wlan_channel__title"                    => "LC__CMDB__CATS__ACCESS_POINT_CHANNEL",
            "isys_wlan_auth__title"                       => "LC__CMDB__CATS__ACCESS_POINT_AUTH",
            "isys_wlan_encryption__title"                 => "LC__CMDB__CATS__ACCESS_POINT_ENCRYPTION",
            "isys_cats_access_point_list__broadcast_ssid" => "LC__CMDB__CATS__ACCESS_POINT_BRODCAST_SSID",
            "isys_cats_access_point_list__mac_filter"     => "LC__CMDB__CATS__ACCESS_POINT_MAC_FILTER"

        ];
    }
}
