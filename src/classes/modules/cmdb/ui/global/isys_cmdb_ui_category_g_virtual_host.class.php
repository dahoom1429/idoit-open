<?php

/**
 * @package     i-doit
 * @subpackage
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_virtual_host extends isys_cmdb_ui_category_global
{
    /**
     *
     * @param   isys_cmdb_dao_category_g_virtual_host &$p_cat
     *
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();
        $l_dao_con = new isys_cmdb_dao_connection($p_cat->get_database_component());

        $l_rules['C__CATG__VIRTUAL_HOST__TITLE']['p_strValue'] = $l_catdata["isys_catg_virtual_host_list__title"];
        $l_rules['C__CATG__VIRTUAL_HOST__YES_NO']['p_arData'] = get_smarty_arr_YES_NO();
        $l_rules['C__CATG__VIRTUAL_HOST__YES_NO']['p_strSelectedID'] = $l_catdata["isys_catg_virtual_host_list__virtual_host"];
        $l_rules['C__CATG__VIRTUAL_HOST__LICENSE_SERVER']['p_strValue'] = $l_dao_con->get_object_id_by_connection($l_catdata["isys_catg_virtual_host_list__license_server"]);
        $l_rules['C__CATG__VIRTUAL_HOST__ADMINISTRATION_SERVICE']['p_strValue'] = $l_dao_con->get_object_id_by_connection($l_catdata["isys_catg_virtual_host_list__administration_service"]);
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_virtual_host_list__description"];

        // Get guest systems.
        $l_dao_guests = new isys_cmdb_dao_category_g_guest_systems($p_cat->get_database_component());
        $l_guests = $l_dao_guests->get_data(null, $_GET[C__CMDB__GET__OBJECT], " AND (guest.isys_obj__status = '" . C__RECORD_STATUS__NORMAL . "')");

        if ($l_guests->num_rows() > 0) {
            $l_objects = [];
            $l_message = sprintf(isys_application::instance()->container->get('language')
                ->get("LC__CMDB__CATG__VIRTUAL_HOST_DISSOLVE"), $l_guests->num_rows());

            while ($l_row = $l_guests->get_row()) {
                $l_objects[] = $l_row["isys_obj__id"];
            }

            $data = [
                'headline' => isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__VIRTUAL_HOST_DISSOLVE_HEADLINE'),
                'message'  => $l_message,
                'objects'  => $l_objects,
            ];

            $l_rules['C__CATG__VIRTUAL_HOST__YES_NO']['p_onChange'] = "if (this.value == 0) get_popup('objectpurge', null, 540, 480, {parameters:'" . base64_encode(isys_format_json::encode($data)) . "'});";
        }

        $this->get_template_component()->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    }
}
