<?php

/**
 * i-doit
 *
 * CMDB UI: Global category (category type is global).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_access extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_g_access $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];

        $l_catdata = $p_cat->get_general_data();
        $l_numEntries = $p_cat->get_data(null, $l_obj_id, null, null, C__RECORD_STATUS__NORMAL)
            ->count();

        // Fill in form fields with help of category properties
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Here come some more specific rules
        $l_rules["C__CATG__ACCESS_URL"]["p_strValue"] = ($_POST[C__GET__NAVMODE]) ? $l_catdata["isys_catg_access_list__url"] : isys_helper_link::handle_url_variables(
            $l_catdata["isys_catg_access_list__url"],
            $l_obj_id
        );

        $l_variables = isys_helper_link::get_url_variables($l_obj_id);

        foreach ($l_variables as $l_key => $l_value) {
            $l_variables[$l_key] = (empty($l_value) ? isys_tenantsettings::get('gui.empty_value', '-') : $l_value);
        }

        ksort($l_variables);

        if (($l_numEntries > 0) && (!$l_catdata['isys_catg_access_list__primary'])) {
            $l_rules["C__CATG__ACCESS_PRIMARY"]["p_strSelectedID"] = 0;
        } elseif (($l_numEntries == 0) && (!$l_catdata['isys_catg_access_list__primary'])) {
            $l_rules["C__CATG__ACCESS_PRIMARY"]["p_strSelectedID"] = 1;
        } else {
            $l_rules["C__CATG__ACCESS_PRIMARY"]["p_strSelectedID"] = $l_catdata['isys_catg_access_list__primary'];
        }

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign('accessPlaceholders', $l_variables);
    }
}
