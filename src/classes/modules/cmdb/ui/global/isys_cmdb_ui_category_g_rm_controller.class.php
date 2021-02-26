<?php

/**
 * i-doit
 *
 * UI: global category for Remote Management Controller
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_rm_controller extends isys_cmdb_ui_category_global
{
    /**
     * Process method for displaying the template.
     *
     * @global  array                                  $index_includes
     *
     * @param   isys_cmdb_dao_category_g_rm_controller & $p_cat
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if ($_GET['get_primary_url']) {
            $l_output = $p_cat->dynamic_property_callback_remote_url(['isys_connection__isys_obj__id' => $_POST['rmc_object']]);
            if ($l_output !== null) {
                echo $l_output;
            } else {
                echo isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__RM_CONTROLLER__NO_PRIMARY_URL_DEFINED');
            }
            die;
        }

        // Initializing some variables.
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        // We let the system fill our form-fields.
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_primary_url = isys_cmdb_dao_category_g_access::instance($p_cat->get_database_component())
            ->dynamic_property_callback_primary_url(['isys_obj__id' => $l_catdata['isys_connection__isys_obj__id']]);

        $l_rules["C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT"]["p_strSelectedID"] = $l_catdata['isys_connection__isys_obj__id'];
        $l_rules['C__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY']['p_strValue'] = ($_POST[C__GET__NAVMODE]) ? $l_primary_url : isys_helper_link::handle_url_variables($l_primary_url,
            $l_catdata['isys_connection__isys_obj__id']);

        $l_ajax_param = array_merge($_GET, [
            C__GET__AJAX           => 1,
            C__GET__AJAX_CALL      => 'category',
            'get_primary_url'      => 1,
            C__CMDB__GET__CATLEVEL => $l_catdata['isys_catg_rm_controller_list__id']
        ]);

        $l_link = http_build_query($l_ajax_param, null, '&');

        $this->get_template_component()
            ->assign('rm_controller_ajax_url', '?' . $l_link);
        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
